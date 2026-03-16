<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ConsultaPreciosController extends Controller
{
    private const BASE_URL = 'https://ms-opm.minsa.gob.pe/msopmcovid';

    private const DEPARTAMENTOS = [
        '01' => 'AMAZONAS',
        '02' => 'ANCASH',
        '03' => 'APURIMAC',
        '04' => 'AREQUIPA',
        '05' => 'AYACUCHO',
        '06' => 'CAJAMARCA',
        '07' => 'CALLAO',
        '08' => 'CUSCO',
        '09' => 'HUANCAVELICA',
        '10' => 'HUANUCO',
        '11' => 'ICA',
        '12' => 'JUNIN',
        '13' => 'LA LIBERTAD',
        '14' => 'LAMBAYEQUE',
        '15' => 'LIMA',
        '16' => 'LORETO',
        '17' => 'MADRE DE DIOS',
        '18' => 'MOQUEGUA',
        '19' => 'PASCO',
        '20' => 'PIURA',
        '21' => 'PUNO',
        '22' => 'SAN MARTIN',
        '23' => 'TACNA',
        '24' => 'TUMBES',
        '25' => 'UCAYALI',
    ];

    private function headers(): array
    {
        return [
            'Accept'          => 'application/json, text/plain, */*',
            'Content-Type'    => 'application/json',
            'Origin'          => 'https://opm-digemid.minsa.gob.pe',
            'Referer'         => 'https://opm-digemid.minsa.gob.pe/',
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ];
    }

    public function index(): Response
    {
        $departamentos = self::DEPARTAMENTOS;

        return Inertia::render('ConsultaPrecios/Index', [
            'title'         => 'Consulta de Precios de Medicamentos',
            'departamentos' => $departamentos,
        ]);
    }

    /**
     * Autocomplete de producto para el ciudadano.
     * POST /msopmcovid/producto/autocompleteciudadano
     * Body real: {"filtro":{"nombreProducto":"Parace","pagina":1,"tamanio":10,"tokenGoogle":""}}
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $query = trim($request->input('query', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'snippf_auto_' . md5(strtolower($query));

        $data = Cache::remember($cacheKey, 3600, function () use ($query) {
            try {
                $response = Http::withHeaders($this->headers())
                    ->timeout(15)
                    ->post(self::BASE_URL . '/producto/autocompleteciudadano', [
                        'filtro' => [
                            'nombreProducto' => $query,
                            'pagina'         => 1,
                            'tamanio'        => 15,
                            'tokenGoogle'    => '',
                        ],
                    ]);

                if ($response->successful()) {
                    $body = $response->json();
                    return $body['data'] ?? [];
                }

                \Log::warning('SNIPPF autocomplete HTTP ' . $response->status() . ': ' . $response->body());
            } catch (\Throwable $e) {
                \Log::warning('SNIPPF autocomplete error: ' . $e->getMessage());
            }

            return [];
        });

        return response()->json($data);
    }

    /**
     * Listado de provincias por departamento.
     * POST /msopmcovid/parametro/provincias
     * Body real: {"filtro":{"codigo":"13","codigoDos":null}}
     */
    public function provincias(Request $request): JsonResponse
    {
        $codigoDepartamento = $request->input('codigoDepartamento', '');

        if (empty($codigoDepartamento)) {
            return response()->json([]);
        }

        $cacheKey = 'snippf_prov_' . $codigoDepartamento;

        $data = Cache::remember($cacheKey, 86400, function () use ($codigoDepartamento) {
            try {
                $response = Http::withHeaders($this->headers())
                    ->timeout(15)
                    ->post(self::BASE_URL . '/parametro/provincias', [
                        'filtro' => [
                            'codigo'    => $codigoDepartamento,
                            'codigoDos' => null,
                        ],
                    ]);

                if ($response->successful()) {
                    $body = $response->json();
                    return $body['data'] ?? [];
                }

                \Log::warning('SNIPPF provincias HTTP ' . $response->status() . ': ' . $response->body());
            } catch (\Throwable $e) {
                \Log::warning('SNIPPF provincias error: ' . $e->getMessage());
            }

            return [];
        });

        return response()->json($data);
    }

    /**
     * Listado de distritos por provincia.
     * POST /msopmcovid/parametro/distritos
     * Body real: {"filtro":{"codigo":"01","codigoDos":"13"}}
     *   codigo    = codigoProvincia
     *   codigoDos = codigoDepartamento
     */
    public function distritos(Request $request): JsonResponse
    {
        $codigoDepartamento = $request->input('codigoDepartamento', '');
        $codigoProvincia    = $request->input('codigoProvincia', '');

        if (empty($codigoDepartamento) || empty($codigoProvincia)) {
            return response()->json([]);
        }

        $cacheKey = 'snippf_dist_' . $codigoDepartamento . '_' . $codigoProvincia;

        $data = Cache::remember($cacheKey, 86400, function () use ($codigoDepartamento, $codigoProvincia) {
            try {
                $response = Http::withHeaders($this->headers())
                    ->timeout(15)
                    ->post(self::BASE_URL . '/parametro/distritos', [
                        'filtro' => [
                            'codigo'    => $codigoProvincia,
                            'codigoDos' => $codigoDepartamento,
                        ],
                    ]);

                if ($response->successful()) {
                    $body = $response->json();
                    return $body['data'] ?? [];
                }

                \Log::warning('SNIPPF distritos HTTP ' . $response->status() . ': ' . $response->body());
            } catch (\Throwable $e) {
                \Log::warning('SNIPPF distritos error: ' . $e->getMessage());
            }

            return [];
        });

        return response()->json($data);
    }

    /**
     * Buscar precios de medicamentos.
     * POST /msopmcovid/preciovista/ciudadano
     */
    public function buscar(Request $request): JsonResponse
    {
        $request->validate([
            'codigoProducto'      => 'required|integer',
            'codigoDepartamento'  => 'required|string|max:2',
            'concent'             => 'nullable|string|max:50',
            'codGrupoFF'          => 'nullable|string|max:5',
            'tokenGoogle'         => 'nullable|string',
        ]);

        $filtro = [
            'codigoProducto'           => (int) $request->input('codigoProducto'),
            'codigoDepartamento'       => $request->input('codigoDepartamento'),
            'codigoProvincia'          => $request->input('codigoProvincia'),
            'codigoUbigeo'             => $request->input('codigoUbigeo'),
            'codTipoEstablecimiento'   => $request->input('codTipoEstablecimiento') ?: null,
            'catEstablecimiento'       => $request->input('catEstablecimiento'),
            'nombreEstablecimiento'    => $request->input('nombreEstablecimiento'),
            'nombreLaboratorio'        => $request->input('nombreLaboratorio'),
            'codGrupoFF'               => $request->input('codGrupoFF'),
            'concent'                  => $request->input('concent') ?? '',
            'tamanio'                  => 100,
            'pagina'                   => (int) $request->input('pagina', 1),
            'tokenGoogle'              => $request->input('tokenGoogle', ''),
            'nombreProducto'           => null,
        ];

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->post(self::BASE_URL . '/preciovista/ciudadano', [
                    'filtro' => $filtro,
                ]);

            if ($response->successful()) {
                $body = $response->json();

                if (isset($body['codigo']) && $body['codigo'] === '00') {
                    return response()->json([
                        'success' => true,
                        'data'    => $body['data'] ?? [],
                        'total'   => $body['cantidad'] ?? 0,
                        'entidad' => $body['entidad'] ?? null,
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $body['mensaje'] ?? 'Error al consultar el servicio DIGEMID.',
                    'data'    => [],
                    'total'   => 0,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'El servicio DIGEMID no está disponible en este momento (HTTP ' . $response->status() . ').',
                'data'    => [],
                'total'   => 0,
            ], 200);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar al servicio DIGEMID. Verifique su conexión a internet.',
                'data'    => [],
                'total'   => 0,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('SNIPPF buscar error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error inesperado al consultar precios.',
                'data'    => [],
                'total'   => 0,
            ], 200);
        }
    }

    /**
     * Detalle de un establecimiento para un producto específico.
     * POST /msopmcovid/precioproducto/obtener
     * Body real: {"filtro":{"codigoProducto":52547,"codEstablecimiento":"0022412","tokenGoogle":""}}
     *   codigoProducto   = codProdE  (del listado de precios)
     *   codEstablecimiento = codEstab (del listado de precios)
     */
    public function detalle(Request $request): JsonResponse
    {
        $request->validate([
            'codigoProducto'     => 'required|integer',
            'codEstablecimiento' => 'required|string|max:20',
            'tokenGoogle'        => 'nullable|string',
        ]);

        $codigoProducto     = (int) $request->input('codigoProducto');
        $codEstablecimiento = $request->input('codEstablecimiento');

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post(self::BASE_URL . '/precioproducto/obtener', [
                    'filtro' => [
                        'codigoProducto'     => $codigoProducto,
                        'codEstablecimiento' => $codEstablecimiento,
                        'tokenGoogle'        => $request->input('tokenGoogle', ''),
                    ],
                ]);

            if ($response->successful()) {
                $body = $response->json();

                if (isset($body['codigo']) && $body['codigo'] === '00') {
                    return response()->json([
                        'success' => true,
                        'data'    => $body['entidad'] ?? null,
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $body['mensaje'] ?? 'Error al obtener el detalle.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Servicio DIGEMID no disponible (HTTP ' . $response->status() . ').',
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('SNIPPF detalle error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el detalle del establecimiento.',
            ], 200);
        }
    }

    // --- Rutas alternativas con query string para compatibilidad cPanel ---

    public function buscarByQuery(Request $request): JsonResponse
    {
        return $this->buscar($request);
    }

    public function autocompleteByQuery(Request $request): JsonResponse
    {
        return $this->autocomplete($request);
    }

    public function provinciasByQuery(Request $request): JsonResponse
    {
        return $this->provincias($request);
    }

    public function distritosByQuery(Request $request): JsonResponse
    {
        return $this->distritos($request);
    }

    public function detalleByQuery(Request $request): JsonResponse
    {
        return $this->detalle($request);
    }
}
