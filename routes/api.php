<?php

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AsistenciaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BimestreController;
use App\Http\Controllers\Api\ConsolidadoController;
use App\Http\Controllers\Api\CalificacionController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\EstudianteController;
use App\Http\Controllers\Api\GradoController;
use App\Http\Controllers\Api\MatriculaController;
use App\Http\Controllers\Api\NivelController;
use App\Http\Controllers\Api\ApoderadoController;
use App\Http\Controllers\Api\PadreController;
use App\Http\Controllers\Api\ParentescoController;
use App\Http\Controllers\Api\PeriodoController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\SeccionController;
use App\Http\Controllers\Api\TipoDocumentoController;
use App\Http\Controllers\Api\TipoEvaluacionController;
use App\Http\Controllers\Api\AuditoriaController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\NecesidadEspecialController;
use App\Http\Controllers\Api\PersonalController;
use App\Http\Controllers\Api\AsistenciaPersonalController;
use App\Http\Controllers\Api\EstadisticaController;
use App\Http\Controllers\Api\SiagieController;
use App\Http\Controllers\Api\TipoMatriculaController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ============ AUTH (Sanctum) ============
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum','throttle:300,1'])->group(function () {
    Route::get('/user', function (Illuminate\Http\Request $request) {
        return $request->user();
    });

    // ============ CATÁLOGOS (tablas maestras) ============
    Route::prefix('roles')->whereNumber('id')->group(function () {
        Route::get('/', [RolController::class, 'index']);
        Route::get('/{id}', [RolController::class, 'show']);
        Route::post('/', [RolController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [RolController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [RolController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [RolController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [RolController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('estados')->whereNumber('id')->group(function () {
        Route::get('/', [EstadoController::class, 'index']);
        Route::get('/{id}', [EstadoController::class, 'show']);
        Route::post('/', [EstadoController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [EstadoController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [EstadoController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [EstadoController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [EstadoController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('niveles')->whereNumber('id')->group(function () {
        Route::get('/', [NivelController::class, 'index']);
        Route::get('/{id}', [NivelController::class, 'show']);
        Route::post('/', [NivelController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [NivelController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [NivelController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [NivelController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [NivelController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('grados')->whereNumber('id')->group(function () {
        Route::get('/', [GradoController::class, 'index']);
        Route::get('/{id}', [GradoController::class, 'show']);
        Route::post('/', [GradoController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [GradoController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [GradoController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [GradoController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [GradoController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('areas')->whereNumber('id')->group(function () {
        Route::get('/', [AreaController::class, 'index']);
        Route::get('/{id}', [AreaController::class, 'show']);
        Route::post('/', [AreaController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [AreaController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [AreaController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [AreaController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [AreaController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('docentes')->whereNumber('id')->group(function () {
        Route::get('/', [DocenteController::class, 'index']);
        Route::get('/{id}', [DocenteController::class, 'show']);
        Route::post('/', [DocenteController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [DocenteController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [DocenteController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [DocenteController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [DocenteController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('secciones')->whereNumber('id')->group(function () {
        Route::get('/', [SeccionController::class, 'index']);
        Route::get('/{id}', [SeccionController::class, 'show']);
        Route::get('/{id}/vacantes', [SeccionController::class, 'vacantes']);
        Route::post('/', [SeccionController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [SeccionController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [SeccionController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [SeccionController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [SeccionController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('personal')->whereNumber('id')->group(function () {
        Route::get('/', [PersonalController::class, 'index']);
        Route::get('/{id}', [PersonalController::class, 'show']);
        Route::get('/{id}/asistencias', [PersonalController::class, 'asistencias']);
        Route::post('/', [PersonalController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [PersonalController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [PersonalController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [PersonalController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [PersonalController::class, 'restore'])->middleware('admin');
        Route::get('/{id}/asistencias', [PersonalController::class, 'asistencias']);
    });

    Route::prefix('tipos-matricula')->whereNumber('id')->group(function () {
        Route::get('/', [TipoMatriculaController::class, 'index']);
        Route::get('/{id}', [TipoMatriculaController::class, 'show']);
        Route::post('/', [TipoMatriculaController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [TipoMatriculaController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [TipoMatriculaController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [TipoMatriculaController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [TipoMatriculaController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('tipos-evaluacion')->whereNumber('id')->group(function () {
        Route::get('/', [TipoEvaluacionController::class, 'index']);
        Route::get('/{id}', [TipoEvaluacionController::class, 'show']);
        Route::post('/', [TipoEvaluacionController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [TipoEvaluacionController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [TipoEvaluacionController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [TipoEvaluacionController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [TipoEvaluacionController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('tipos-documento')->whereNumber('id')->group(function () {
        Route::get('/', [TipoDocumentoController::class, 'index']);
        Route::get('/{id}', [TipoDocumentoController::class, 'show']);
        Route::post('/', [TipoDocumentoController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [TipoDocumentoController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [TipoDocumentoController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [TipoDocumentoController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [TipoDocumentoController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('parentescos')->whereNumber('id')->group(function () {
        Route::get('/', [ParentescoController::class, 'index']);
        Route::get('/{id}', [ParentescoController::class, 'show']);
        Route::post('/', [ParentescoController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [ParentescoController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [ParentescoController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [ParentescoController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [ParentescoController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('periodos')->whereNumber('id')->group(function () {
        Route::get('/', [PeriodoController::class, 'index']);
        Route::get('/{id}', [PeriodoController::class, 'show']);
        Route::post('/', [PeriodoController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [PeriodoController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [PeriodoController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [PeriodoController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [PeriodoController::class, 'restore'])->middleware('admin');
        Route::post('/{id}/activar', [PeriodoController::class, 'activar'])->middleware('admin');
        Route::post('/{id}/promocion', [PeriodoController::class, 'promocion'])->middleware('admin');
        Route::post('/{id}/bimestres/generar', [PeriodoController::class, 'generarBimestres'])->middleware('admin');
    });

    Route::prefix('configuraciones')->whereNumber('id')->group(function () {
        Route::get('/', [ConfiguracionController::class, 'index']);
        Route::get('/{id}', [ConfiguracionController::class, 'show']);
        Route::post('/', [ConfiguracionController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [ConfiguracionController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [ConfiguracionController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [ConfiguracionController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [ConfiguracionController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('usuarios')->whereNumber('id')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [UserController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [UserController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [UserController::class, 'restore'])->middleware('admin');
    });

    // ============ APODERADOS HUB (optimización padres-estudiantes) ============
    Route::prefix('apoderados')->whereNumber('id')->group(function () {
        Route::get('/', [ApoderadoController::class, 'index']);
        Route::get('/{id}', [ApoderadoController::class, 'show']);
        Route::post('/', [ApoderadoController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [ApoderadoController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [ApoderadoController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [ApoderadoController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [ApoderadoController::class, 'restore'])->middleware('admin');
        Route::get('/{id}/padres', [ApoderadoController::class, 'padres']);
        Route::get('/{id}/estudiantes', [ApoderadoController::class, 'estudiantes']);
        Route::post('/{id}/padres', [ApoderadoController::class, 'attachPadre']);
        Route::delete('/{id}/padres/{padreId}', [ApoderadoController::class, 'detachPadre'])->whereNumber('padreId');
        Route::post('/{id}/estudiantes', [ApoderadoController::class, 'attachEstudiante']);
        Route::delete('/{id}/estudiantes/{estudianteId}', [ApoderadoController::class, 'detachEstudiante'])->whereNumber('estudianteId');
    });

    // ============ CORE TRANSACCIONAL ============
    Route::prefix('estudiantes')->whereNumber('id')->group(function () {
        Route::get('/', [EstudianteController::class, 'index']);
        Route::get('/{id}', [EstudianteController::class, 'show']);
        Route::post('/', [EstudianteController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [EstudianteController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [EstudianteController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [EstudianteController::class, 'destroy'])->middleware('admin');
        Route::get('/{id}/informe-progreso', [EstudianteController::class, 'informeProgreso'])->whereNumber('id');
        Route::get('/{id}/informe-progreso.pdf', [EstudianteController::class, 'informeProgresoPdf'])->whereNumber('id');
        Route::post('/{id}/restore', [EstudianteController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('padres')->whereNumber('id')->group(function () {
        Route::get('/', [PadreController::class, 'index']);
        Route::get('/{id}', [PadreController::class, 'show']);
        Route::post('/', [PadreController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [PadreController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [PadreController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [PadreController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [PadreController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('matriculas')->whereNumber('id')->group(function () {
        Route::get('/', [MatriculaController::class, 'index']);
        Route::post('/', [MatriculaController::class, 'store']);
        Route::post('/registro', [MatriculaController::class, 'registro']);
        Route::get('/{id}', [MatriculaController::class, 'show']);
        Route::put('/{id}', [MatriculaController::class, 'update']);
        Route::patch('/{id}', [MatriculaController::class, 'update']);
        Route::delete('/{id}', [MatriculaController::class, 'destroy']);
        Route::post('/{id}/restore', [MatriculaController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('bimestres')->whereNumber('id')->group(function () {
        Route::get('/', [BimestreController::class, 'index']);
        Route::get('/activo', [BimestreController::class, 'activo']);
        Route::put('/{id}', [BimestreController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [BimestreController::class, 'update'])->middleware('admin');
        Route::post('/{id}/activar', [BimestreController::class, 'activar'])->middleware('admin');
    });

    Route::get('/consolidado', [ConsolidadoController::class, 'index']);
    Route::post('/consolidado/batch', [ConsolidadoController::class, 'batch']);

    Route::prefix('calificaciones')->whereNumber('id')->group(function () {
        Route::get('/', [CalificacionController::class, 'index']);
        Route::post('/', [CalificacionController::class, 'store']);
        Route::get('/{id}', [CalificacionController::class, 'show']);
        Route::put('/{id}', [CalificacionController::class, 'update']);
        Route::patch('/{id}', [CalificacionController::class, 'update']);
        Route::delete('/{id}', [CalificacionController::class, 'destroy']);
        Route::post('/{id}/restore', [CalificacionController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('asistencias')->whereNumber('id')->group(function () {
        Route::get('/', [AsistenciaController::class, 'index']);
        Route::post('/', [AsistenciaController::class, 'store']);
        Route::post('/batch', [AsistenciaController::class, 'batch']);
        Route::get('/{id}', [AsistenciaController::class, 'show']);
        Route::put('/{id}', [AsistenciaController::class, 'update']);
        Route::patch('/{id}', [AsistenciaController::class, 'update']);
        Route::delete('/{id}', [AsistenciaController::class, 'destroy']);
        Route::post('/{id}/restore', [AsistenciaController::class, 'restore'])->middleware('admin');
    });

    Route::prefix('asistencias-personal')->group(function () {
        Route::get('/', [AsistenciaPersonalController::class, 'index']);
        Route::post('/', [AsistenciaPersonalController::class, 'store']);
        Route::post('/marcar', [AsistenciaPersonalController::class, 'marcar']);
        Route::get('/{id}', [AsistenciaPersonalController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [AsistenciaPersonalController::class, 'update'])->whereNumber('id');
        Route::patch('/{id}', [AsistenciaPersonalController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [AsistenciaPersonalController::class, 'destroy'])->whereNumber('id');
        Route::post('/{id}/restore', [AsistenciaPersonalController::class, 'restore'])->whereNumber('id')->middleware('admin');
        Route::post('/{id}/justificar', [AsistenciaPersonalController::class, 'justificar'])->whereNumber('id');
    });

    Route::prefix('documentos')->whereNumber('id')->group(function () {
        Route::get('/', [DocumentoController::class, 'index']);
        Route::get('/{id}', [DocumentoController::class, 'show']);
        Route::post('/', [DocumentoController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [DocumentoController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [DocumentoController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [DocumentoController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [DocumentoController::class, 'restore'])->middleware('admin');
    });

    // ============ NECESIDADES ESPECIALES / SEHO / SAANEE (entrevista EGA + RVM 107-2015 + RVM 035-2024) ============
    Route::prefix('necesidades-especiales')->whereNumber('id')->group(function () {
        Route::get('/', [NecesidadEspecialController::class, 'index']);
        Route::get('/{id}', [NecesidadEspecialController::class, 'show']);
        Route::post('/', [NecesidadEspecialController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [NecesidadEspecialController::class, 'update'])->middleware('admin');
        Route::patch('/{id}', [NecesidadEspecialController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [NecesidadEspecialController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [NecesidadEspecialController::class, 'restore'])->middleware('admin');
    });

    // ============ SIAGIE IMPORT/EXPORT (plantilla nombre inmutable + nota C con motivo + AD/A/B/C) ============
    Route::prefix('siagie')->group(function () {
        Route::post('/import', [SiagieController::class, 'import'])->middleware('admin');
        Route::get('/export/{seccion}', [SiagieController::class, 'export'])->whereNumber('seccion');
        Route::get('/export/{seccion}/{periodo}', [SiagieController::class, 'export'])->whereNumber('seccion')->whereNumber('periodo');
    });

    // ============ REPORTES DUAL (on-demand + cache nocturno 02:00) ============
    Route::prefix('reportes')->whereNumber('id')->group(function () {
        Route::get('/', [ReporteController::class, 'index']); // historial filtrable (default último año)
        Route::post('/', [ReporteController::class, 'store']); // generar on-demand
        Route::get('/{id}', [ReporteController::class, 'show']);
        Route::get('/{id}/descargar', [ReporteController::class, 'descargar']);
        Route::delete('/{id}', [ReporteController::class, 'destroy'])->middleware('admin');
        Route::post('/{id}/restore', [ReporteController::class, 'restore'])->middleware('admin');
    });

    // ============ ESTADÍSTICAS (KPIs + gráficos dashboard) ============
    Route::prefix('estadisticas')->group(function () {
        Route::get('/dashboard', [EstadisticaController::class, 'dashboard']);
        Route::get('/matriculas-por-nivel', [EstadisticaController::class, 'matriculasPorNivel']);
        Route::get('/logros-cneb', [EstadisticaController::class, 'logrosCneb']);
        Route::get('/asistencia-mensual', [EstadisticaController::class, 'asistenciaMensual']);
        Route::get('/ocupacion-secciones', [EstadisticaController::class, 'ocupacionSecciones']);
    });

    // ============ AUDITORÍA (solo ADMIN) ============
    Route::prefix('auditorias')->middleware('admin')->whereNumber('id')->group(function () {
        Route::get('/', [AuditoriaController::class, 'index']);
        Route::get('/{id}', [AuditoriaController::class, 'show']);
    });
});