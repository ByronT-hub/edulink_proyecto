<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TestController extends Controller
{
    public function testCrearCurso(Request $request)
    {
        try {
            Log::info('TEST: Intentando crear curso', ['data' => $request->all()]);
            
            // Verificar que la tabla existe y tiene las columnas correctas
            $columns = Schema::getColumnListing('cursos');
            Log::info('TEST: Columnas de la tabla cursos', ['columns' => $columns]);
            
            // Verificar que el maestro existe
            $maestro = \App\Models\Maestro::find(1);
            Log::info('TEST: Maestro encontrado', ['maestro' => $maestro ? $maestro->toArray() : null]);
            
            $data = [
                'titulo' => 'Curso de Prueba Manual',
                'descripcion' => 'Descripción de prueba detallada',
                'precio' => 99.99,
                'duracion' => 10,
                'categoria' => 'test',
                'nivel' => 'principiante',
                'requisitos' => 'ninguno',
                'maestro_id' => 1
            ];
            
            Log::info('TEST: Datos a insertar', ['data' => $data]);
            
            // Intentar inserción manual
            $curso = new \App\Models\Curso();
            foreach ($data as $key => $value) {
                $curso->$key = $value;
            }
            
            Log::info('TEST: Antes de save()');
            $result = $curso->save();
            Log::info('TEST: Después de save()', ['result' => $result, 'curso_id' => $curso->id]);
            
            return response()->json([
                'message' => 'TEST: Curso creado exitosamente',
                'curso' => $curso,
                'data_sent' => $data,
                'columns' => $columns
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('TEST: Error al crear curso', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'TEST: Error interno del servidor',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
