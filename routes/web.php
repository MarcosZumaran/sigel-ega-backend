<?php

use App\Http\Controllers\ApoderadoWebController;
use App\Http\Controllers\AsistenciaWebController;
use App\Http\Controllers\BuscarWebController;
use App\Http\Controllers\EstudianteWebController;
use App\Http\Controllers\GradoWebController;
use App\Http\Controllers\MatriculaWebController;
use App\Http\Controllers\NotaWebController;
use App\Http\Controllers\PadreWebController;
use App\Http\Controllers\PeriodoWebController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteWebController;
use App\Http\Controllers\EstadisticaWebController;
use App\Http\Controllers\SeccionWebController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $periodo = \App\Models\Periodo::where('activo', true)->first();
    return Inertia::render('Dashboard', [
        'kpis' => [
            'estudiantes' => \App\Models\Estudiante::count(),
            'padres' => \App\Models\Padre::count(),
            'apoderados' => \App\Models\Apoderado::count(),
            'matriculas' => \App\Models\Matricula::count(),
            'docentes' => \App\Models\Docente::count(),
            'asistencias_hoy' => \App\Models\Asistencia::whereDate('fecha', today())->count(),
        ],
        'periodo_activo' => $periodo,
        'matriculas_hoy' => \App\Models\Matricula::whereDate('created_at', today())->count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Apoderados: nodo hub (se crean automáticamente al registrar padres; sin creación manual)
    Route::get('/apoderados', [ApoderadoWebController::class, 'index'])->name('apoderados.index');
    Route::get('/apoderados/{id}', [ApoderadoWebController::class, 'show'])->whereNumber('id')->name('apoderados.show');
    Route::delete('/apoderados/{id}', [ApoderadoWebController::class, 'destroy'])->whereNumber('id')->name('apoderados.destroy');
    Route::post('/apoderados/{id}/padres', [ApoderadoWebController::class, 'attachPadre'])->whereNumber('id')->name('apoderados.padres.attach');
    Route::delete('/apoderados/{id}/padres/{padreId}', [ApoderadoWebController::class, 'detachPadre'])->whereNumber(['id', 'padreId'])->name('apoderados.padres.detach');
    Route::post('/apoderados/{id}/estudiantes', [ApoderadoWebController::class, 'attachEstudiante'])->whereNumber('id')->name('apoderados.estudiantes.attach');
    Route::delete('/apoderados/{id}/estudiantes/{estudianteId}', [ApoderadoWebController::class, 'detachEstudiante'])->whereNumber(['id', 'estudianteId'])->name('apoderados.estudiantes.detach');

    // CRUDs gestión escolar (secretaría / académica)
    Route::post('/padres/{id}/restore', [PadreWebController::class, 'restore'])->whereNumber('id')->name('padres.restore');
    Route::resource('padres', PadreWebController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('/estudiantes/{id}/restore', [EstudianteWebController::class, 'restore'])->whereNumber('id')->name('estudiantes.restore');
    Route::resource('estudiantes', EstudianteWebController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('/matriculas/{id}/restore', [MatriculaWebController::class, 'restore'])->whereNumber('id')->name('matriculas.restore');
    Route::post('/matriculas/registro', [MatriculaWebController::class, 'registro'])->name('matriculas.registro');
    Route::resource('matriculas', MatriculaWebController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::resource('notas', NotaWebController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::resource('asistencias', AsistenciaWebController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    Route::resource('grados', GradoWebController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('/grados/{id}/restore', [GradoWebController::class, 'restore'])->whereNumber('id')->name('grados.restore');

    Route::resource('secciones', SeccionWebController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('/secciones/{id}/restore', [SeccionWebController::class, 'restore'])->whereNumber('id')->name('secciones.restore');

    Route::resource('periodos', PeriodoWebController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('/periodos/{id}/restore', [PeriodoWebController::class, 'restore'])->whereNumber('id')->name('periodos.restore');
    Route::post('/periodos/{id}/activar', [PeriodoWebController::class, 'activar'])->whereNumber('id')->name('periodos.activar');
    Route::post('/periodos/{id}/promocion', [PeriodoWebController::class, 'promocion'])->whereNumber('id')->name('periodos.promocion');

    // Reportes y estadísticas
    Route::post('/reportes/{id}/restore', [ReporteWebController::class, 'restore'])->whereNumber('id')->name('reportes.restore');
    Route::resource('reportes', ReporteWebController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::get('/estadisticas', [EstadisticaWebController::class, 'index'])->name('estadisticas');

    // Buscador global + endpoints JSON del formulario único de matrícula
    Route::get('/buscar', [BuscarWebController::class, 'index'])->name('buscar');
    Route::get('/buscar/estudiantes', [BuscarWebController::class, 'estudiantes'])->name('buscar.estudiantes');
    Route::get('/buscar/padres', [BuscarWebController::class, 'padres'])->name('buscar.padres');
    Route::get('/secciones/{id}/vacantes', [BuscarWebController::class, 'vacantesSeccion'])->whereNumber('id')->name('secciones.vacantes');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
