<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CursoController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\MetricasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MaestroController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\EstructuraController;
use App\Http\Controllers\ProgresoController;

// Ruta de prueba
Route::get('/ping', function () {
    return response()->json(['message' => 'API OK desde edulink_backend']);
});


// ======================
// RUTAS PÚBLICAS
// ======================

Route::get('/cursos', [CursoController::class, 'index']);
Route::get('/cursos/{id}/estructura', [EstructuraController::class, 'show']);
Route::post('/cursos/{id}/estructura', [EstructuraController::class, 'storeOrUpdate']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);


// ======================
// RUTAS PROTEGIDAS
// ======================

Route::middleware(['simple_auth'])->group(function () {

    // AUTH
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // INSCRIPCIONES
    Route::post('/cursos/{id}/inscribirse', [InscripcionController::class, 'inscribirse']);
    Route::get('/mis-cursos', [InscripcionController::class, 'misCursos']);
    Route::get('/inscripciones/{id}', [InscripcionController::class, 'detalles']);
    Route::post('/inscripciones', [InscripcionController::class, 'store']);
    Route::get('/inscripciones', [InscripcionController::class, 'index']);

    // Obtener inscripción según curso (usado en frontend)
    Route::get('/inscripciones/curso/{cursoId}', [InscripcionController::class, 'findByCourse']);

    // PERFIL
    Route::put('/estudiantes/{id}/perfil', [EstudianteController::class, 'updatePerfil']);

    // PAGOS
    Route::post('/pagos/autorizar', [PagoController::class, 'autorizar']);
    Route::get('/pagos/recibo/{pago_id}', [PagoController::class, 'recibo']);
    Route::get('/pagos/mis-pagos', [PagoController::class, 'misPagos']);

    // ======================
    // PROGRESO DEL CURSO
    // (❗ESTAS SON LAS QUE TE FALTABAN)
    // ======================
    Route::get('/progresos/{inscripcionId}', [ProgresoController::class, 'show']);
    Route::put('/progresos/{inscripcionId}', [ProgresoController::class, 'update']);

    // ======================
    // CERTIFICADOS
    // ======================
    Route::get('/certificados/{id}', [CertificadoController::class, 'show']);
    Route::get('/mis-certificados', [CertificadoController::class, 'misCertificados']);
    Route::get('/certificados/{inscripcionId}/descargar', [CertificadoController::class, 'descargar']);
});

