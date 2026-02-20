<?php

namespace App\Http\Controllers;

use App\Models\NotaVenta;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class NotaVentaController extends Controller
{
    public function index(): View
    {
        $notas = NotaVenta::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('pages.notas-venta.index', [
            'title' => 'Notas de venta',
            'notas' => $notas,
        ]);
    }

    public function create(): View
    {
        return view('pages.notas-venta.create', ['title' => 'Nueva nota de venta']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ruc' => ['nullable', 'string', 'max:32'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string'],
            'sucursal' => ['nullable', 'string', 'max:255'],
            'cliente_nombre' => ['required', 'string', 'max:255'],
            'cliente_dni_ruc' => ['required', 'string', 'max:32'],
            'cliente_direccion' => ['nullable', 'string'],
            'boleta_numero' => ['required', 'string', 'max:64'],
            'boleta_fecha_emision' => ['required', 'string', 'max:20'],
            'boleta_fecha_vencimiento' => ['nullable', 'string', 'max:20'],
            'boleta_moneda' => ['nullable', 'string', 'max:10'],
            'boleta_forma_pago' => ['nullable', 'string', 'max:64'],
            'descuento_total' => ['nullable', 'numeric', 'min:0'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'igv' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'notas' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.descripcion' => ['required', 'string'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:0'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'detalles.*.descuento_unitario' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.importe_total_unitario' => ['required', 'numeric', 'min:0'],
        ], [], [
            'cliente_nombre' => 'nombre del cliente',
            'cliente_dni_ruc' => 'DNI/RUC del cliente',
            'boleta_numero' => 'número de documento',
            'detalles' => 'al menos un detalle',
        ]);

        $detalles = [];
        foreach ($request->input('detalles') as $d) {
            $detalles[] = [
                'descripcion' => $d['descripcion'] ?? '',
                'cantidad' => (float) ($d['cantidad'] ?? 0),
                'precio_unitario' => (float) ($d['precio_unitario'] ?? 0),
                'descuento_unitario' => (float) ($d['descuento_unitario'] ?? 0),
                'importe_total_unitario' => (float) ($d['importe_total_unitario'] ?? 0),
            ];
        }

        $nota = NotaVenta::create([
            'user_id' => Auth::id(),
            'numero_documento' => $validated['boleta_numero'],
            'ruc' => $validated['ruc'] ?? null,
            'razon_social' => $validated['razon_social'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'sucursal' => $validated['sucursal'] ?? null,
            'cliente' => [
                'nombre' => $validated['cliente_nombre'],
                'dni_ruc' => $validated['cliente_dni_ruc'],
                'direccion' => $validated['cliente_direccion'] ?? '',
            ],
            'boleta' => [
                'numero' => $validated['boleta_numero'],
                'fecha_emision' => $validated['boleta_fecha_emision'],
                'fecha_vencimiento' => $validated['boleta_fecha_vencimiento'] ?? $validated['boleta_fecha_emision'],
                'moneda' => $validated['boleta_moneda'] ?? 'Soles',
                'forma_pago' => $validated['boleta_forma_pago'] ?? 'Contado',
            ],
            'detalles' => $detalles,
            'descuento_total' => (float) ($validated['descuento_total'] ?? 0),
            'subtotal' => (float) ($validated['subtotal'] ?? 0),
            'igv' => (float) ($validated['igv'] ?? 0),
            'total' => (float) $validated['total'],
            'notas' => $validated['notas'] ?? null,
        ]);

        if ($request->has('generar_pdf') && $request->boolean('generar_pdf')) {
            return redirect()->route('notas-venta.pdf', ['id' => $nota->id])
                ->with('success', 'Nota de venta guardada. Generando PDF.');
        }

        return redirect()->route('notas-venta.index')
            ->with('success', 'Nota de venta guardada correctamente.');
    }

    public function show(NotaVenta $notaVenta): View|RedirectResponse
    {
        if ($notaVenta->user_id !== Auth::id()) {
            abort(404);
        }

        return view('pages.notas-venta.show', [
            'title' => 'Nota de venta ' . $notaVenta->numero_documento,
            'nota' => $notaVenta,
        ]);
    }

    /**
     * PDF por query string (?id=2). Ruta fuera del middleware auth para evitar 404 en cPanel.
     * La autenticación y validación se hacen aquí.
     */
    public function pdfByQuery(Request $request): Response|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin')
                ->with('error', 'Inicia sesión y usa el enlace PDF desde el listado de notas de venta.');
        }

        $id = $request->query('id');
        if ($id === null || $id === '' || (int) $id < 1) {
            return redirect()->route('notas-venta.index')
                ->with('error', 'Falta el número de nota. Usa el enlace PDF del listado.');
        }

        $notaVenta = NotaVenta::find((int) $id);
        if (! $notaVenta) {
            return redirect()->route('notas-venta.index')
                ->with('error', 'No se encontró esa nota de venta.');
        }

        try {
            return $this->pdf($notaVenta);
        } catch (\Throwable $e) {
            report($e);
            $message = 'No se pudo generar el PDF. Vuelve a intentarlo.';
            if (config('app.debug')) {
                $message .= ' (' . $e->getMessage() . ')';
            }
            return redirect()->route('notas-venta.index')
                ->with('error', $message);
        }
    }

    /**
     * PDF por ID en la ruta (notas-venta/pdf/{id}).
     */
    public function pdfById(int $id)
    {
        $notaVenta = NotaVenta::findOrFail($id);
        return $this->pdf($notaVenta);
    }

    public function pdf(NotaVenta $notaVenta)
    {

        $data = $this->prepareDataForPdf($notaVenta);
        $pdf = Pdf::loadView('pdf.nota-venta', $data);
        $pdf->setPaper('a4');

        // Nombre de archivo seguro para descarga
        $filename = 'nota_venta_' . preg_replace('/[^a-zA-Z0-9\-_]/', '_', $notaVenta->numero_documento) . '.pdf';

        $response = $pdf->download($filename);
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    private function prepareDataForPdf(NotaVenta $nota): array
    {
        $cliente = $nota->cliente ?? [];
        $boleta = $nota->boleta ?? [];
        $detalles = $nota->detalles ?? [];

        $items = [];
        foreach ($detalles as $d) {
            $items[] = [
                'quantity' => $d['cantidad'] ?? 0,
                'description' => $d['descripcion'] ?? '',
                'unitPrice' => number_format((float) ($d['precio_unitario'] ?? 0), 2, '.', ''),
                'discount' => number_format((float) ($d['descuento_unitario'] ?? 0), 2, '.', ''),
                'amount' => number_format((float) ($d['importe_total_unitario'] ?? 0), 2, '.', ''),
            ];
        }

        $formatDate = function ($dateStr) {
            if (empty($dateStr)) return '';
            $d = \Carbon\Carbon::parse($dateStr);
            return $d->format('Y-m-d');
        };

        $currency = $boleta['moneda'] ?? 'Soles';
        $currencySymbol = (strtoupper($currency) === 'DÓLARES' || stripos($currency, 'dolar') !== false) ? 'US$' : 'S/';

        return [
            'companyName' => $nota->razon_social ?? '',
            'companyRuc' => $nota->ruc ?? '',
            'companyAddress' => $nota->direccion ?? '',
            'sucursal' => $nota->sucursal ?? '',
            'clientName' => $cliente['nombre'] ?? '',
            'clientRuc' => $cliente['dni_ruc'] ?? '',
            'clientAddress' => $cliente['direccion'] ?? '',
            'noteNumber' => $boleta['numero'] ?? $nota->numero_documento,
            'date' => $formatDate($boleta['fecha_emision'] ?? null),
            'dueDate' => $formatDate($boleta['fecha_vencimiento'] ?? null),
            'currency' => $currency,
            'currencySymbol' => $currencySymbol,
            'paymentMethod' => $boleta['forma_pago'] ?? 'Contado',
            'items' => $items,
            'subtotal' => number_format((float) $nota->subtotal, 2, '.', ''),
            'discount' => number_format((float) $nota->descuento_total, 2, '.', ''),
            'tax' => number_format((float) $nota->igv, 2, '.', ''),
            'taxRate' => 18,
            'total' => number_format((float) $nota->total, 2, '.', ''),
            'notes' => $nota->notas ?? '',
            'logoBase64' => $this->getLogoBase64(),
        ];
    }

    private function getLogoBase64(): string
    {
        $paths = [
            ['path' => public_path('logo_cpromed.jpg'), 'mime' => 'image/jpeg'],
            ['path' => public_path('images/logo/logo.png'), 'mime' => 'image/png'],
        ];
        foreach ($paths as $item) {
            try {
                if (file_exists($item['path']) && is_readable($item['path'])) {
                    $data = @file_get_contents($item['path']);
                    if ($data !== false) {
                        return 'data:' . $item['mime'] . ';base64,' . base64_encode($data);
                    }
                }
            } catch (\Throwable) {
                continue;
            }
        }
        return '';
    }
}
