<?php

use App\Models\Maestro;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

Route::get('/debug-token', function() {
    $token = request()->bearerToken();
    
    if (!$token) {
        return response()->json(['error' => 'No token provided']);
    }
    
    // Verificar en sesión
    $sessionPayload = \Illuminate\Support\Facades\Session::get('auth_tokens.' . $token);
    
    // Buscar en todas las tablas
    $userInDB = null;
    $tables = ['users', 'maestros', 'estudiantes'];
    
    foreach ($tables as $table) {
        if ($table === 'users') {
            $user = \App\Models\User::where('remember_token', $token)->first();
        } elseif ($table === 'maestros') {
            $user = \App\Models\Maestro::where('remember_token', $token)->first();
        } else {
            $user = \App\Models\Estudiante::where('remember_token', $token)->first();
        }
        
        if ($user) {
            $userInDB = ['table' => $table, 'user' => $user];
            break;
        }
    }
    
    return response()->json([
        'token' => $token,
        'session_payload' => $sessionPayload,
        'user_in_db' => $userInDB,
        'validation_result' => \App\Http\Controllers\AuthController::validateToken($token)
    ]);
});

Route::get('/create-test-maestro', function() {
    try {
        // Verificar si ya existe
        $existing = Maestro::where('correo', 'osmar@gmail.com')->first();
        if ($existing) {
            return response()->json([
                'message' => 'Maestro ya existe',
                'maestro_id' => $existing->id
            ]);
        }

        // Crear maestro
        $maestro = Maestro::create([
            'nombre' => 'Osmar',
            'apellido' => 'Garcia',
            'correo' => 'osmar@gmail.com',
            'contrasena' => Hash::make('12345678'),
            'especialidad' => 'Desarrollo Web',
            'experiencia' => '5 años'
        ]);

        return response()->json([
            'message' => 'Maestro creado exitosamente',
            'maestro_id' => $maestro->id
        ]);

    } catch (Exception $e) {
        return response()->json([
            'error' => 'Error al crear maestro: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/test-curso-create', function() {
    try {
        $maestro = Maestro::first();
        if (!$maestro) {
            return response()->json(['error' => 'No hay maestros disponibles'], 404);
        }

        $data = [
            'titulo' => 'Curso de Prueba',
            'descripcion' => 'Descripción del curso',
            'precio' => 99.99,
            'duracion' => 30,
            'categoria' => 'Prueba',
            'nivel' => 'Principiante',
            'requisitos' => 'Ninguno',
            'maestro_id' => $maestro->id
        ];

        $curso = \App\Models\Curso::create($data);

        return response()->json([
            'success' => true,
            'curso' => $curso
        ]);

    } catch (Exception $e) {
        return response()->json([
            'error' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});