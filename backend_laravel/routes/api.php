<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Importar tus controladores:
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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Todas las rutas se exponen bajo /api/
|
*/

// Ruta de prueba opcional
Route::get('/ping', function () {
    return response()->json(['message' => 'API OK desde edulink_backend']);
});

// Test temporal
Route::post('/test/crear-curso', [TestController::class, 'testCrearCurso']);

// === RUTAS PÚBLICAS ===
// Lista de cursos (público)
Route::get('/cursos', [CursoController::class, 'index']);

// Autenticación
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);



// ======================================================
// === RUTAS PROTEGIDAS — SOLO USUARIOS AUTENTICADOS ====
// ======================================================
Route::middleware(['simple_auth'])->group(function () {

    // --- Autenticación ---
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // --- Inscripciones ---
    Route::post('/cursos/{id}/inscribirse', [InscripcionController::class, 'inscribirse']);
    Route::get('/mis-cursos', [InscripcionController::class, 'misCursos']);
    Route::get('/inscripciones/{id}', [InscripcionController::class, 'detalles']);
    Route::post('/inscripciones', [InscripcionController::class, 'store']);
    Route::get('/inscripciones', [InscripcionController::class, 'index']);
    
    // --- Perfil del estudiante ---
    Route::put('/estudiantes/{id}/perfil', [EstudianteController::class, 'updatePerfil']);
    
    // --- Pagos (estudiantes) ---
    Route::post('/pagos/autorizar', [PagoController::class, 'autorizar']);
    Route::get('/pagos/mis-pagos', [PagoController::class, 'misPagos']); // ← MOVIDO AQUÍ

    // --- Certificados ---
    Route::get('/certificados/{id}', [CertificadoController::class, 'show']);
    Route::get('/mis-certificados', [CertificadoController::class, 'misCertificados']);
});



// ======================================================
// === RUTAS DE MAESTROS (maestro o admin) ==============
// ======================================================
Route::middleware(['simple_auth'])->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Inscripciones
    Route::post('/cursos/{id}/inscribirse', [InscripcionController::class, 'inscribirse']);
    Route::get('/mis-cursos', [InscripcionController::class, 'misCursos']);
    Route::get('/inscripciones/{id}', [InscripcionController::class, 'detalles']);
    Route::post('/inscripciones', [InscripcionController::class, 'store']);
    Route::get('/inscripciones', [InscripcionController::class, 'index']);

    // Pagos
    Route::post('/pagos/autorizar', [PagoController::class, 'autorizar']);
    Route::get('/pagos/mis-pagos', [PagoController::class, 'misPagos']);

    // Certificados
    Route::get('/certificados/{id}', [CertificadoController::class, 'show']);
    Route::get('/mis-certificados', [CertificadoController::class, 'misCertificados']);
});




// ======================================================
// === RUTAS DE ADMINISTRADOR (solo admin) ==============
// ======================================================
Route::middleware(['role:admin'])->group(function () {

    // Gestión de usuarios
    Route::get('/admin/usuarios', [AdminController::class, 'getUsuarios']);
    Route::post('/admin/usuarios', [AdminController::class, 'crearUsuario']);
    Route::delete('/admin/usuarios/{tipo}/{id}', [AdminController::class, 'eliminarUsuario']);
    
    // Gestión de cursos
    Route::post('/cursos', [CursoController::class, 'store']);
    Route::put('/cursos/{id}', [CursoController::class, 'update']);
    Route::delete('/cursos/{id}', [CursoController::class, 'destroy']);
    
    // Gestión de estudiantes
    Route::get('/estudiantes', [EstudianteController::class, 'index']);
    Route::get('/estudiantes/{id}', [EstudianteController::class, 'show']);
    
    // Métricas (Laravel → Rails)
    Route::get('/metricas/cursos', [MetricasController::class, 'cursos']);
    
    // Reportes
    Route::get('/reportes/inscripciones', [InscripcionController::class, 'reportes']);
    Route::get('/reportes/pagos', [PagoController::class, 'reportes']);
});
