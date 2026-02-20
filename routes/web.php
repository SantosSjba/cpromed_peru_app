<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\NotaVentaController;

/*
|--------------------------------------------------------------------------
| Rutas de autenticación (invitados)
|--------------------------------------------------------------------------
*/
Route::get('/signin', [AuthController::class, 'showLoginForm'])->name('signin');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/signup', [AuthController::class, 'showRegisterForm'])->name('signup');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Prueba de enrutado en cPanel: si ves "OK" aquí, Laravel y mod_rewrite funcionan (borrar en producción)
Route::get('test-pdf-route', function () {
    return response('OK - Laravel enruta correctamente. Puedes borrar esta ruta después.', 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
});

// Prueba de generación PDF (solo si estás logueado): descarga un PDF de prueba para verificar DomPDF en el servidor
Route::get('test-pdf-download', function () {
    if (! auth()->check()) {
        return redirect()->route('signin')->with('error', 'Inicia sesión para probar el PDF.');
    }
    try {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>Prueba PDF</h1><p>Si descargas este archivo, DomPDF funciona en el servidor.</p>');
        $pdf->setPaper('a4');
        return $pdf->download('test-pdf.pdf');
    } catch (\Throwable $e) {
        return response('Error DomPDF: ' . $e->getMessage(), 500, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
})->name('test-pdf-download');

// PDF sin middleware auth: la autenticación se comprueba en el controlador (evita 404 en algunos cPanel)
Route::get('descargar-nota-pdf', [NotaVentaController::class, 'pdfByQuery'])->name('notas-venta.pdf');

/*
|--------------------------------------------------------------------------
| Rutas de la aplicación (requieren autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('pages.dashboard.ecommerce', ['title' => 'Dashboard']);
    })->name('dashboard');

    Route::get('/calendar', function () {
        return view('pages.calender', ['title' => 'Calendario']);
    })->name('calendar');

    Route::get('/profile', function () {
        return view('pages.profile', ['title' => 'Perfil']);
    })->name('profile');

    // Notas de venta (migrado desde admintopico)
    Route::get('notas-venta/pdf/{id}', [NotaVentaController::class, 'pdfById'])->name('notas-venta.pdf.id');
    Route::get('notas-venta/{notaVenta}/pdf', [NotaVentaController::class, 'pdf'])->name('notas-venta.pdf.slug');
    Route::resource('notas-venta', NotaVentaController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('/form-elements', function () {
        return view('pages.form.form-elements', ['title' => 'Form Elements']);
    })->name('form-elements');

    Route::get('/basic-tables', function () {
        return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
    })->name('basic-tables');

    Route::get('/blank', function () {
        return view('pages.blank', ['title' => 'Blank']);
    })->name('blank');

    Route::get('/error-404', function () {
        return view('pages.errors.error-404', ['title' => 'Error 404']);
    })->name('error-404');

    Route::get('/line-chart', function () {
        return view('pages.chart.line-chart', ['title' => 'Line Chart']);
    })->name('line-chart');

    Route::get('/bar-chart', function () {
        return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
    })->name('bar-chart');

    Route::get('/alerts', function () {
        return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
    })->name('alerts');

    Route::get('/avatars', function () {
        return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
    })->name('avatars');

    Route::get('/badge', function () {
        return view('pages.ui-elements.badges', ['title' => 'Badges']);
    })->name('badges');

    Route::get('/buttons', function () {
        return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
    })->name('buttons');

    Route::get('/image', function () {
        return view('pages.ui-elements.images', ['title' => 'Images']);
    })->name('images');

    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', ['title' => 'Videos']);
    })->name('videos');

    // Cepromed: módulos de negocio (placeholders)
    Route::get('/pacientes', fn () => view('pages.blank', ['title' => 'Pacientes']))->name('pacientes.index');
    Route::get('/pacientes/crear', fn () => view('pages.blank', ['title' => 'Agregar Paciente']))->name('pacientes.crear');
    Route::get('/pacientes/registro', fn () => view('pages.blank', ['title' => 'Registro de Pacientes']))->name('pacientes.registro');
    Route::get('/citas/crear', fn () => view('pages.blank', ['title' => 'Reservar Cita']))->name('citas.crear');
    Route::get('/medicos', fn () => view('pages.blank', ['title' => 'Médicos']))->name('medicos.index');
    Route::get('/medicos/crear', fn () => view('pages.blank', ['title' => 'Agregar Médico']))->name('medicos.crear');
    Route::get('/medicos/turnos', fn () => view('pages.blank', ['title' => 'Gestión de Turnos']))->name('medicos.turnos');
    Route::get('/registros/nacimiento', fn () => view('pages.blank', ['title' => 'Registro de Nacimiento']))->name('registros.nacimiento');
    Route::get('/registros/defuncion', fn () => view('pages.blank', ['title' => 'Registro de Defunción']))->name('registros.defuncion');
    Route::get('/farmacia', fn () => view('pages.blank', ['title' => 'Farmacia']))->name('farmacia.index');
    Route::get('/farmacia/crear', fn () => view('pages.blank', ['title' => 'Agregar Medicamento']))->name('farmacia.crear');
    Route::get('/inventario', fn () => view('pages.blank', ['title' => 'Inventario']))->name('inventario.index');
    Route::get('/inventario/emitidos', fn () => view('pages.blank', ['title' => 'Artículos Emitidos']))->name('inventario.emitidos');
});
