<?php

namespace App\Http\Controllers;

use App\Models\ControlPesoConfig;
use App\Models\ControlPesoRegistro;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class ControlPesoController extends Controller
{
    // ─── Index: lista de pacientes con seguimiento ────────────────────────────

    public function index(Request $request): Response
    {
        $buscar = is_string($request->input('buscar')) ? trim($request->input('buscar')) : '';

        // Pacientes que ya tienen configuración de control de peso
        $conSeguimiento = ControlPesoConfig::where('control_peso_configs.user_id', Auth::id())
            ->join('pacientes', 'pacientes.id', '=', 'control_peso_configs.paciente_id')
            ->select('control_peso_configs.*')
            ->with(['paciente', 'registros'])
            ->get()
            ->map(function (ControlPesoConfig $cfg) {
                $registros = $cfg->registros->sortBy('fecha')->values();
                $ultimo    = $registros->last();
                $imc       = $ultimo
                    ? ControlPesoConfig::calcularImc($ultimo->peso, $cfg->talla)
                    : ControlPesoConfig::calcularImc($cfg->peso_inicial, $cfg->talla);
                $categoria = ControlPesoConfig::categoriaImc($imc);
                $perdido   = $ultimo ? round($cfg->peso_inicial - $ultimo->peso, 2) : 0;
                $progreso  = ($cfg->peso_meta && ($cfg->peso_inicial - $cfg->peso_meta) > 0)
                    ? round(($perdido / ($cfg->peso_inicial - $cfg->peso_meta)) * 100, 1)
                    : null;

                return [
                    'id'              => $cfg->id,
                    'paciente_id'     => $cfg->paciente_id,
                    'paciente_nombre' => $cfg->paciente->nombre_completo ?? '—',
                    'paciente_dni'    => $cfg->paciente->dni ?? '—',
                    'peso_inicial'    => $cfg->peso_inicial,
                    'peso_actual'     => $ultimo ? $ultimo->peso : $cfg->peso_inicial,
                    'peso_meta'       => $cfg->peso_meta,
                    'talla'           => $cfg->talla,
                    'imc'             => $imc,
                    'imc_categoria'   => $categoria['label'],
                    'imc_color'       => $categoria['color'],
                    'kg_perdidos'     => $perdido,
                    'progreso_pct'    => $progreso,
                    'registros_count' => $registros->count(),
                    'fecha_inicio'    => $cfg->fecha_inicio?->format('Y-m-d'),
                ];
            })
            ->filter(function ($item) use ($buscar) {
                if ($buscar === '') {
                    return true;
                }
                $q = mb_strtolower($buscar);

                return str_contains(mb_strtolower($item['paciente_nombre']), $q)
                    || str_contains(mb_strtolower($item['paciente_dni']), $q);
            })
            ->values();

        // Pacientes sin seguimiento (para el selector de "Iniciar seguimiento")
        $idsConSeguimiento = ControlPesoConfig::where('user_id', Auth::id())
            ->pluck('paciente_id')
            ->toArray();

        $sinSeguimiento = Paciente::where('user_id', Auth::id())
            ->whereNotIn('id', $idsConSeguimiento)
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'dni', 'fecha_nacimiento', 'genero'])
            ->map(fn ($p) => [
                'id'     => $p->id,
                'nombre' => $p->nombre_completo,
                'dni'    => $p->dni ?? '',
            ])
            ->values();

        return Inertia::render('ControlPeso/Index', [
            'title'           => 'VitaTrack',
            'conSeguimiento'  => $conSeguimiento,
            'sinSeguimiento'  => $sinSeguimiento,
            'buscar'          => $buscar,
        ]);
    }

    // ─── Show: tracker completo de un paciente ────────────────────────────────

    public function showByQuery(Request $request): Response|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin');
        }

        $id  = (int) $request->query('id');
        $cfg = ControlPesoConfig::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['paciente', 'registros'])
            ->first();

        if (! $cfg) {
            return redirect()->route('control-peso.index')->with('error', 'Seguimiento no encontrado.');
        }

        $registros = $cfg->registros->sortBy('fecha')->values();

        // Construir tabla con cálculos
        $tabla = $registros->map(function (ControlPesoRegistro $r, int $idx) use ($cfg, $registros) {
            $imc       = ControlPesoConfig::calcularImc($r->peso, $cfg->talla);
            $categoria = ControlPesoConfig::categoriaImc($imc);
            $pctCambio = $cfg->peso_inicial > 0
                ? round((($r->peso - $cfg->peso_inicial) / $cfg->peso_inicial) * 100, 1)
                : 0;

            return [
                'id'         => $r->id,
                'semana'     => $idx + 1,
                'fecha'      => $r->fecha->format('Y-m-d'),
                'peso'       => $r->peso,
                'pct_cambio' => $pctCambio,
                'imc'        => $imc,
                'imc_cat'    => $categoria['label'],
                'imc_color'  => $categoria['color'],
                'notas'      => $r->notas ?? '',
            ];
        })->values();

        $pesoActual  = $registros->last()?->peso ?? $cfg->peso_inicial;
        $imcActual   = ControlPesoConfig::calcularImc($pesoActual, $cfg->talla);
        $catActual   = ControlPesoConfig::categoriaImc($imcActual);
        $kgPerdidos  = round($cfg->peso_inicial - $pesoActual, 2);
        $progresoPct = ($cfg->peso_meta && ($cfg->peso_inicial - $cfg->peso_meta) > 0)
            ? min(100, round(($kgPerdidos / ($cfg->peso_inicial - $cfg->peso_meta)) * 100, 1))
            : null;

        // Revisar recompensas automáticamente
        $recompensas = $cfg->recompensas ?? [];
        foreach ($recompensas as &$r) {
            $kgRequired = (float) ($r['kg_perdidos'] ?? 0);
            if (! $r['done'] && $kgPerdidos >= $kgRequired && $kgRequired > 0) {
                // No marcamos automáticamente; el usuario elige
            }
        }
        unset($r);

        return Inertia::render('ControlPeso/Show', [
            'title'      => 'VitaTrack · ' . ($cfg->paciente->nombre_completo ?? '—'),
            'config'     => [
                'id'           => $cfg->id,
                'paciente_id'  => $cfg->paciente_id,
                'peso_inicial' => $cfg->peso_inicial,
                'talla'        => $cfg->talla,
                'peso_meta'    => $cfg->peso_meta,
                'fecha_inicio' => $cfg->fecha_inicio?->format('Y-m-d'),
                'fecha_meta'   => $cfg->fecha_meta?->format('Y-m-d'),
                'recompensas'  => $recompensas,
            ],
            'paciente'   => [
                'id'     => $cfg->paciente->id,
                'nombre' => $cfg->paciente->nombre_completo ?? '—',
                'dni'    => $cfg->paciente->dni ?? '',
                'edad'   => $cfg->paciente->edad_calculada,
                'genero' => $cfg->paciente->genero ?? '',
            ],
            'tabla'      => $tabla,
            'resumen'    => [
                'peso_actual'   => $pesoActual,
                'imc_actual'    => $imcActual,
                'imc_categoria' => $catActual['label'],
                'imc_color'     => $catActual['color'],
                'kg_perdidos'   => $kgPerdidos,
                'progreso_pct'  => $progresoPct,
            ],
        ]);
    }

    // ─── Crear / actualizar configuración ─────────────────────────────────────

    public function configurar(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin');
        }

        $data = $request->validate([
            'paciente_id'  => ['required', 'integer', 'exists:pacientes,id'],
            'peso_inicial' => ['required', 'numeric', 'min:1', 'max:500'],
            'talla'        => ['required', 'numeric', 'min:50', 'max:300'],
            'peso_meta'    => ['nullable', 'numeric', 'min:1', 'max:500'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_meta'   => ['nullable', 'date', 'after:fecha_inicio'],
            'recompensas'  => ['nullable', 'array', 'max:10'],
            'recompensas.*.kg_perdidos'  => ['nullable', 'numeric', 'min:0'],
            'recompensas.*.descripcion'  => ['nullable', 'string', 'max:255'],
            'recompensas.*.done'         => ['boolean'],
        ]);

        // Verificar que el paciente pertenece al usuario
        $paciente = Paciente::where('id', $data['paciente_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cfg = ControlPesoConfig::updateOrCreate(
            ['user_id' => Auth::id(), 'paciente_id' => $paciente->id],
            [
                'peso_inicial' => $data['peso_inicial'],
                'talla'        => $data['talla'],
                'peso_meta'    => $data['peso_meta'] ?? null,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_meta'   => $data['fecha_meta'] ?? null,
                'recompensas'  => $data['recompensas'] ?? null,
            ]
        );

        return redirect(route('control-peso.ver') . '?id=' . $cfg->id)
            ->with('success', 'Configuración guardada.');
    }

    // ─── Nuevo paciente rápido desde el tracker ───────────────────────────────

    public function crearPacienteRapido(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin');
        }

        $data = $request->validate([
            'nombres'          => ['required', 'string', 'max:255'],
            'apellidos'        => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'genero'           => ['required', 'string', 'in:masculino,femenino,otro'],
            'dni'              => ['nullable', 'string', 'max:32'],
            'celular'          => ['nullable', 'string', 'max:32'],
        ]);

        $paciente = Paciente::create([
            ...$data,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('control-peso.index')
            ->with('success', 'Paciente creado. Ahora configura su seguimiento.')
            ->with('nuevo_paciente_id', $paciente->id);
    }

    // ─── Agregar registro de peso ─────────────────────────────────────────────

    public function guardarRegistro(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin');
        }

        $data = $request->validate([
            'config_id' => ['required', 'integer'],
            'fecha'     => ['required', 'date'],
            'peso'      => ['required', 'numeric', 'min:1', 'max:500'],
            'notas'     => ['nullable', 'string', 'max:500'],
        ]);

        $cfg = ControlPesoConfig::where('id', $data['config_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        ControlPesoRegistro::create([
            'config_id' => $cfg->id,
            'fecha'     => $data['fecha'],
            'peso'      => $data['peso'],
            'notas'     => $data['notas'] ?? null,
        ]);

        return redirect(route('control-peso.ver') . '?id=' . $cfg->id)
            ->with('success', 'Registro agregado.');
    }

    // ─── Eliminar registro de peso ────────────────────────────────────────────

    public function eliminarRegistro(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin');
        }

        $registroId = (int) $request->input('registro_id');
        $registro   = ControlPesoRegistro::findOrFail($registroId);
        $cfg        = $registro->config;

        if ($cfg->user_id !== Auth::id()) {
            abort(403);
        }

        $configId = $cfg->id;
        $registro->delete();

        return redirect(route('control-peso.ver') . '?id=' . $configId)
            ->with('success', 'Registro eliminado.');
    }

    // ─── Actualizar recompensas (marcar done) ─────────────────────────────────

    public function actualizarRecompensas(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin');
        }

        $data = $request->validate([
            'config_id'   => ['required', 'integer'],
            'recompensas' => ['required', 'array'],
            'recompensas.*.kg_perdidos' => ['nullable', 'numeric'],
            'recompensas.*.descripcion' => ['nullable', 'string', 'max:255'],
            'recompensas.*.done'        => ['boolean'],
        ]);

        $cfg = ControlPesoConfig::where('id', $data['config_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cfg->update(['recompensas' => $data['recompensas']]);

        return redirect(route('control-peso.ver') . '?id=' . $cfg->id)
            ->with('success', 'Recompensas actualizadas.');
    }

    // ─── Eliminar seguimiento completo ────────────────────────────────────────

    public function eliminarSeguimiento(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin');
        }

        $id  = (int) $request->input('config_id');
        $cfg = ControlPesoConfig::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cfg->delete();

        return redirect()->route('control-peso.index')
            ->with('success', 'Seguimiento eliminado.');
    }

    // ─── Enlace público: el paciente ve su avance y registra peso (sin usuario/contraseña) ───

    /** GET /seguimiento/{token} — Vista pública por token. */
    public function showPublicByToken(string $token): Response|RedirectResponse
    {
        $cfg = ControlPesoConfig::where('share_token', $token)
            ->with(['paciente', 'registros'])
            ->first();

        if (! $cfg) {
            return redirect()->route('signin')->with('error', 'Enlace no válido o expirado.');
        }

        $registros = $cfg->registros->sortBy('fecha')->values();

        $tabla = $registros->map(function (ControlPesoRegistro $r, int $idx) use ($cfg, $registros) {
            $imc       = ControlPesoConfig::calcularImc($r->peso, $cfg->talla);
            $categoria = ControlPesoConfig::categoriaImc($imc);
            $pctCambio = $cfg->peso_inicial > 0
                ? round((($r->peso - $cfg->peso_inicial) / $cfg->peso_inicial) * 100, 1)
                : 0;

            return [
                'id'         => $r->id,
                'semana'     => $idx + 1,
                'fecha'      => $r->fecha->format('Y-m-d'),
                'peso'       => $r->peso,
                'pct_cambio' => $pctCambio,
                'imc'        => $imc,
                'imc_cat'    => $categoria['label'],
                'imc_color'  => $categoria['color'],
                'notas'      => $r->notas ?? '',
            ];
        })->values();

        $pesoActual  = $registros->last()?->peso ?? $cfg->peso_inicial;
        $imcActual   = ControlPesoConfig::calcularImc($pesoActual, $cfg->talla);
        $catActual   = ControlPesoConfig::categoriaImc($imcActual);
        $kgPerdidos  = round($cfg->peso_inicial - $pesoActual, 2);
        $progresoPct = ($cfg->peso_meta && ($cfg->peso_inicial - $cfg->peso_meta) > 0)
            ? min(100, round(($kgPerdidos / ($cfg->peso_inicial - $cfg->peso_meta)) * 100, 1))
            : null;

        return Inertia::render('ControlPeso/PublicShow', [
            'title'    => 'Mi seguimiento · VitaTrack',
            'token'    => $token,
            'config'   => [
                'id'           => $cfg->id,
                'peso_inicial' => $cfg->peso_inicial,
                'talla'        => $cfg->talla,
                'peso_meta'    => $cfg->peso_meta,
                'fecha_inicio' => $cfg->fecha_inicio?->format('Y-m-d'),
                'fecha_meta'   => $cfg->fecha_meta?->format('Y-m-d'),
            ],
            'paciente' => [
                'nombre' => $cfg->paciente->nombre_completo ?? '—',
            ],
            'tabla'    => $tabla,
            'resumen'  => [
                'peso_actual'   => $pesoActual,
                'imc_actual'    => $imcActual,
                'imc_categoria' => $catActual['label'],
                'imc_color'     => $catActual['color'],
                'kg_perdidos'   => $kgPerdidos,
                'progreso_pct'  => $progresoPct,
            ],
        ]);
    }

    /** POST /seguimiento/{token}/registro — El paciente registra su peso (sin auth). */
    public function guardarRegistroPublico(Request $request, string $token): RedirectResponse
    {
        $cfg = ControlPesoConfig::where('share_token', $token)->first();

        if (! $cfg) {
            return redirect()->route('signin')->with('error', 'Enlace no válido.');
        }

        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'peso'  => ['required', 'numeric', 'min:1', 'max:500'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        ControlPesoRegistro::create([
            'config_id' => $cfg->id,
            'fecha'     => $data['fecha'],
            'peso'      => $data['peso'],
            'notas'     => $data['notas'] ?? null,
        ]);

        return redirect()->route('seguimiento.publico', ['token' => $token])
            ->with('success', 'Registro guardado correctamente.');
    }

    /** POST — Generar o obtener enlace público para el paciente (requiere auth). */
    public function generarEnlace(Request $request): \Illuminate\Http\JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'No autorizado.'], 403);
        }

        $configId = (int) $request->input('config_id');
        $cfg      = ControlPesoConfig::where('id', $configId)->where('user_id', Auth::id())->firstOrFail();

        if (empty($cfg->share_token)) {
            $cfg->share_token = ControlPesoConfig::generarShareToken();
            $cfg->save();
        }

        $url = URL::route('seguimiento.publico', ['token' => $cfg->share_token], true);

        return response()->json(['url' => $url, 'token' => $cfg->share_token]);
    }
}
