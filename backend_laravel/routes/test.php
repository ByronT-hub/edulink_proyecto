<?php

use Illuminate\Support\Facades\Route;
use App\Models\Curso;
use App\Models\Maestro;
use Illuminate\Http\Request;

Route::get('/test-curso-creation', function () {
    try {
        // Encontrar un maestro existente
        $maestro = Maestro::first();
        
        if (!$maestro) {
            return response()->json(['error' => 'No hay maestros en la base de datos'], 404);
        }

        // Datos de prueba
        $data = [
            'titulo' => 'Curso de Prueba',
            'descripcion' => 'Descripción del curso de prueba',
            'precio' => 99.99,
            'duracion' => 40,
            'categoria' => 'Programación',
            'nivel' => 'Intermedio',
            'requisitos' => 'Conocimientos básicos de programación',
            'maestro_id' => $maestro->id
        ];

        // Crear curso
        $curso = Curso::create($data);

        return response()->json([
            'success' => true,
            'mensaje' => 'Curso creado exitosamente',
            'curso' => $curso
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al crear curso: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});