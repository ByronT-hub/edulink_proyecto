<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;


class CursoController extends Controller
{

    /**
     * Listar cursos por maestro (solo admin)
     */
    public function cursosPorMaestro($maestro_id)
    {
        $cursos = Curso::where('maestro_id', $maestro_id)->get();
        return response()->json($cursos);
    }
    /**
     * Listar cursos activos (público)
     */
    public function index()
    {
        $cursos = Curso::where('activo', true)->get();

        return response()->json($cursos);
    }

    /**
     * Crear nuevo curso (maestro)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'duracion' => 'required|integer|min:1',
            'categoria' => 'nullable|string|max:100',
            'nivel' => 'nullable|string|max:50',
            'requisitos' => 'nullable|string',
        ]);

        // Asignar el maestro_id desde el usuario autenticado
        $user = $request->user();
        $data['maestro_id'] = $user ? $user->id : null;
        $data['activo'] = true;

        $curso = \App\Models\Curso::create($data);

        return response()->json($curso, 201);
    }

    /**
     * Actualizar curso (solo admin)
     */
    public function update(Request $request, $id)
    {
        $curso = Curso::findOrFail($id);

        $data = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'costo_centavos' => 'sometimes|integer|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'sometimes|boolean',
        ]);

        $curso->update($data);

        return response()->json($curso);
    }

    /**
     * Eliminar curso (solo admin)
     */
    public function destroy($id)
    {
        $curso = Curso::findOrFail($id);
        
        // Verificar que no tenga inscripciones activas
        if ($curso->inscripciones()->count() > 0) {
            return response()->json([
                'error' => 'No se puede eliminar un curso con inscripciones existentes'
            ], 409);
        }

        $curso->delete();

        return response()->json([
            'message' => 'Curso eliminado exitosamente'
        ]);
    }
}
