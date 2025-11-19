<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EstudianteController extends Controller
{
    /**
     * Listar todos los estudiantes (solo admin)
     */
    public function index()
    {
        $estudiantes = Estudiante::select('id', 'nombre', 'correo', 'created_at')
                                ->withCount('inscripciones')
                                ->get();

        return response()->json($estudiantes);
    }

    /**
     * Mostrar un estudiante específico (solo admin)
     */
    public function show($id)
    {
        $estudiante = Estudiante::with(['inscripciones.curso', 'certificados'])
                                ->find($id);

        if (!$estudiante) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        return response()->json([
            'id' => $estudiante->id,
            'nombre' => $estudiante->nombre,
            'correo' => $estudiante->correo,
            'created_at' => $estudiante->created_at,
            'inscripciones' => $estudiante->inscripciones,
            'certificados_count' => $estudiante->certificados->count(),
        ]);
    }

    /**
     * Crear nuevo estudiante (registro público - mantiene compatibilidad)
     */
   public function store(Request $request)
{
    $data = $request->validate([
        'nombre'      => 'required|string|max:255',
        'correo'      => 'required|email|unique:estudiantes,correo',
        'contrasena'  => 'required|string|min:6',
        'telefono'    => 'nullable|string|max:20',
        'carnet'      => 'nullable|string|max:50|unique:estudiantes,carnet',
        'carrera'     => 'nullable|string|max:255',
        'universidad' => 'nullable|string|max:255',
        'nivel_estudio' => 'nullable|string|max:100',
        'intereses'   => 'nullable|string|max:500'
    ]);

    // Encriptar contraseña
    $data['contrasena'] = Hash::make($data['contrasena']);

    // Crear estudiante
    $estudiante = Estudiante::create($data);

    return response()->json([
        'message' => 'Estudiante creado exitosamente',
        'estudiante' => $estudiante->only(['id', 'nombre', 'correo'])
    ], 201);
}

    /**
     * Actualizar perfil del estudiante autenticado
     */
    public function updatePerfil(Request $request, $id)
    {
        $user = $request->auth_user;
        
        // Verificar que el estudiante solo puede editar su propio perfil
        if (!$user || $request->auth_role !== 'estudiante' || $user->id != $id) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $estudiante = Estudiante::find($id);
        if (!$estudiante) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        // Validar datos
        $rules = [
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|unique:estudiantes,correo,' . $id,
            'telefono' => 'nullable|string|max:20',
            'carnet' => 'nullable|string|max:50|unique:estudiantes,carnet,' . $id,
            'password' => 'nullable|string|min:6'
        ];

        $data = $request->validate($rules);

        // Si hay nueva contraseña, encriptarla
        if (!empty($data['password'])) {
            $data['contrasena'] = Hash::make($data['password']);
            unset($data['password']);
        } else {
            unset($data['password']);
        }

        // Actualizar estudiante
        $estudiante->update($data);

        // Obtener datos actualizados para respuesta
        $estudianteActualizado = Estudiante::select([
            'id', 'nombre', 'correo', 'telefono', 'carnet', 'created_at'
        ])->find($id);

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'estudiante' => $estudianteActualizado
        ]);
    }
}
