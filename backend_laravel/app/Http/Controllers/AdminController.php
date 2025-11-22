<?php

namespace App\Http\Controllers;

use App\Models\Maestro;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Listar solo los maestros
    public function getMaestros()
    {
        $maestros = \App\Models\Maestro::select('id', 'nombre', 'correo', 'especialidad', 'telefono', 'biografia', 'created_at')
            ->get()
            ->map(function ($maestro) {
                $maestro->tipo = 'maestro';
                $maestro->role = 'maestro';
                return $maestro;
            });
        return response()->json($maestros);
    }

    // Listar solo los estudiantes
    public function getEstudiantes()
    {
        $estudiantes = \App\Models\Estudiante::select('id', 'nombre', 'correo', 'telefono', 'carrera', 'universidad', 'nivel_estudio', 'intereses', 'created_at')
            ->get()
            ->map(function ($estudiante) {
                $estudiante->tipo = 'estudiante';
                $estudiante->role = 'estudiante';
                return $estudiante;
            });
        return response()->json($estudiantes);
    }
    // Listar todos los usuarios
    public function getUsuarios()
    {
        $usuarios = [];
        
        // Obtener administradores
        $admins = User::select('id', 'name as nombre', 'email as correo', 'created_at')
                     ->get()
                     ->map(function ($user) {
                         $user->tipo = 'admin';
                         $user->role = 'admin';
                         return $user;
                     });
        
        // Obtener estudiantes
        $estudiantes = Estudiante::select('id', 'nombre', 'correo', 'created_at')
                                ->get()
                                ->map(function ($estudiante) {
                                    $estudiante->tipo = 'estudiante';
                                    $estudiante->role = 'estudiante';
                                    return $estudiante;
                                });
        
        // Obtener maestros
        $maestros = Maestro::select('id', 'nombre', 'correo', 'especialidad', 'created_at')
                          ->get()
                          ->map(function ($maestro) {
                              $maestro->tipo = 'maestro';
                              $maestro->role = 'maestro';
                              return $maestro;
                          });
        
        // Combinar todos
        $usuarios = collect([])
            ->merge($admins)
            ->merge($estudiantes)
            ->merge($maestros)
            ->sortByDesc('created_at')
            ->values();
        
        return response()->json($usuarios);
    }
    
    // Crear nuevo usuario
    public function crearUsuario(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|max:255',
            'contrasena' => 'required|string|min:6',
            'role' => 'required|in:admin,estudiante,maestro',
            'especialidad' => 'required_if:role,maestro|string|max:255',
            'biografia' => 'nullable|string',
            'telefono' => 'nullable|string|max:20'
        ]);
        
        // Verificar que el correo no existe
        $existeEmail = User::where('email', $data['correo'])->exists() ||
                      Estudiante::where('correo', $data['correo'])->exists() ||
                      Maestro::where('correo', $data['correo'])->exists();
        
        if ($existeEmail) {
            return response()->json([
                'message' => 'El correo ya está registrado'
            ], 422);
        }
        
        $usuario = null;
        
        switch ($data['role']) {
            case 'admin':
                $usuario = User::create([
                    'name' => $data['nombre'],
                    'email' => $data['correo'],
                    'password' => Hash::make($data['contrasena']),
                    'role' => 'admin'
                ]);
                break;
                
            case 'estudiante':
                $usuario = Estudiante::create([
                    'nombre' => $data['nombre'],
                    'correo' => $data['correo'],
                    'contrasena' => Hash::make($data['contrasena'])
                ]);
                break;
                
            case 'maestro':
                $usuario = Maestro::create([
                    'nombre' => $data['nombre'],
                    'correo' => $data['correo'],
                    'contrasena' => Hash::make($data['contrasena']),
                    'especialidad' => $data['especialidad'],
                    'biografia' => $data['biografia'] ?? null,
                    'telefono' => $data['telefono'] ?? null
                ]);
                break;
        }
        
        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ], 201);
    }
    
    // Editar maestro

    // Eliminar usuario
    public function eliminarUsuario($tipo, $id)
    {
        $usuario = null;
        switch ($tipo) {
            case 'admin':
                $usuario = User::find($id);
                break;
            case 'estudiante':
                $usuario = Estudiante::find($id);
                break;
            case 'maestro':
                $usuario = Maestro::find($id);
                break;
        }
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        $usuario->delete();
        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }
}
