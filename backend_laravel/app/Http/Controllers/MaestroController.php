<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Maestro;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MaestroController extends Controller
{
    // Crear nuevo curso
    public function crearCurso(Request $request)
    {
        try {
            Log::info('Intentando crear curso', ['data' => $request->all()]);
            
            $data = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'precio' => 'required|numeric|min:0',
                'duracion' => 'required|integer|min:1',
                'categoria' => 'nullable|string',
                'nivel' => 'required|in:principiante,intermedio,avanzado',
                'requisitos' => 'nullable|string'
            ]);
            
            // Obtener el maestro autenticado del middleware
            $maestro = $request->input('auth_user');
            
            if (!$maestro) {
                return response()->json(['message' => 'Usuario no autenticado'], 401);
            }
            
            // Agregar el maestro_id del usuario autenticado
            $data['maestro_id'] = $maestro->id;
            
            Log::info('Datos validados', ['validated_data' => $data]);
            
            $curso = Curso::create($data);
            
            Log::info('Curso creado', ['curso_id' => $curso->id]);
            
            return response()->json([
                'message' => 'Curso creado exitosamente',
                'curso' => $curso
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error al crear curso', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Obtener cursos del maestro
    public function misCursos($maestro_id)
    {
        $cursos = Curso::where('maestro_id', $maestro_id)
                      ->with('maestro:id,nombre,especialidad')
                      ->get();
        
        return response()->json($cursos);
    }
    
    // Editar curso
    public function editarCurso(Request $request, $id)
    {
        $curso = Curso::find($id);
        
        if (!$curso) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
        
        $data = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'precio' => 'sometimes|numeric|min:0',
            'duracion' => 'sometimes|integer|min:1',
            'categoria' => 'sometimes|nullable|string',
            'nivel' => 'sometimes|in:principiante,intermedio,avanzado',
            'requisitos' => 'sometimes|nullable|string'
        ]);
        
        $curso->update($data);
        
        return response()->json([
            'message' => 'Curso actualizado exitosamente',
            'curso' => $curso->load('maestro')
        ]);
    }
    
    // Eliminar curso
    public function eliminarCurso($id)
    {
        $curso = Curso::find($id);
        
        if (!$curso) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
        
        $curso->delete();
        
        return response()->json([
            'message' => 'Curso eliminado exitosamente'
        ]);
    }
    
    // Buscar estudiantes
    public function buscarEstudiantes(Request $request)
    {
        $query = $request->get('buscar', '');
        $page = $request->get('page', 1);
        $perPage = 12; // Mostrar 12 estudiantes por página
        
        $estudiantesQuery = Estudiante::query()
            ->withCount('inscripciones as cursos_count') // Contar inscripciones como cursos
            ->select('id', 'nombre', 'correo', 'telefono', 'carrera', 'universidad', 'nivel_estudio', 'intereses', 'created_at');
        
        // Si hay término de búsqueda, filtrar
        if (!empty($query)) {
            $estudiantesQuery->where(function($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('correo', 'like', "%{$query}%")
                  ->orWhere('carrera', 'like', "%{$query}%")
                  ->orWhere('universidad', 'like', "%{$query}%")
                  ->orWhere('intereses', 'like', "%{$query}%");
            });
        }
        
        $estudiantes = $estudiantesQuery
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $estudiantes->items(),
            'current_page' => $estudiantes->currentPage(),
            'last_page' => $estudiantes->lastPage(),
            'per_page' => $estudiantes->perPage(),
            'total' => $estudiantes->total(),
            'from' => $estudiantes->firstItem(),
            'to' => $estudiantes->lastItem()
        ]);
    }
    
    // Obtener perfil del maestro
    public function perfil($id)
    {
        $maestro = Maestro::with(['cursos' => function($query) {
            $query->select('id', 'nombre', 'precio', 'nivel', 'maestro_id');
        }])->find($id);
        
        if (!$maestro) {
            return response()->json(['message' => 'Maestro no encontrado'], 404);
        }
        
        return response()->json($maestro);
    }
    
    // Actualizar perfil del maestro
    public function actualizarPerfil(Request $request, $id)
    {
        $maestro = Maestro::find($id);
        
        if (!$maestro) {
            return response()->json(['message' => 'Maestro no encontrado'], 404);
        }
        
        $data = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'especialidad' => 'sometimes|string|max:255',
            'biografia' => 'nullable|string',
            'telefono' => 'nullable|string|max:20'
        ]);
        
        // Si se proporciona nueva contraseña
        if ($request->has('contrasena')) {
            $request->validate([
                'contrasena' => 'string|min:6',
                'contrasena_actual' => 'required|string'
            ]);
            
            if (!Hash::check($request->contrasena_actual, $maestro->contrasena)) {
                return response()->json(['message' => 'Contraseña actual incorrecta'], 422);
            }
            
            $data['contrasena'] = Hash::make($request->contrasena);
        }
        
        $maestro->update($data);
        
        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'maestro' => $maestro
        ]);
    }
}
