<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Maestro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    /**
     * Registro universal
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255',
            'contrasena' => 'required|string|min:6',
            'role' => 'required|string|in:estudiante,maestro,admin',
            'especialidad' => 'required_if:role,maestro|string|max:255',
            'biografia' => 'nullable|string',
            'telefono' => 'required_if:role,estudiante|nullable|string|max:20',
            'carnet' => 'required_if:role,estudiante|nullable|string|max:50|unique:estudiantes,carnet',
            'carrera' => 'required_if:role,estudiante|nullable|string|max:255',
            'universidad' => 'required_if:role,estudiante|nullable|string|max:255',
            'nivel_estudio' => 'required_if:role,estudiante|nullable|string|max:100',
            'intereses' => 'nullable|string|max:500'
        ]);

        // Verificar correo único
        if (
            Estudiante::where('correo', $data['correo'])->exists() ||
            Maestro::where('correo', $data['correo'])->exists() ||
            User::where('email', $data['correo'])->exists()
        ) {
            return response()->json([
                'message' => 'El correo electrónico ya está registrado',
                'errors' => ['correo' => ['El correo electrónico ya está en uso']]
            ], 422);
        }

        // Crear usuario por rol
        if ($data['role'] === 'estudiante') {
            $usuario = Estudiante::create([
                'nombre' => $data['nombre'],
                'correo' => $data['correo'],
                'contrasena' => Hash::make($data['contrasena']),
                'telefono' => $data['telefono'] ?? null,
                'carnet' => $data['carnet'] ?? null,
                'carrera' => $data['carrera'] ?? null,
                'universidad' => $data['universidad'] ?? null,
                'nivel_estudio' => $data['nivel_estudio'] ?? null,
                'intereses' => $data['intereses'] ?? null,
            ]);
            $mensaje = 'Estudiante registrado exitosamente';

        } elseif ($data['role'] === 'maestro') {
            $usuario = Maestro::create([
                'nombre' => $data['nombre'],
                'correo' => $data['correo'],
                'contrasena' => Hash::make($data['contrasena']),
                'especialidad' => $data['especialidad'],
                'biografia' => $data['biografia'] ?? null,
                'telefono' => $data['telefono'] ?? null,
            ]);
            $mensaje = 'Maestro registrado exitosamente';

        } else {
            $usuario = User::create([
                'name' => $data['nombre'],
                'email' => $data['correo'],
                'password' => Hash::make($data['contrasena']),
                'role' => 'admin'
            ]);
            $mensaje = 'Administrador registrado exitosamente';
        }

        // Generar JWT
        $token = $this->generateToken($usuario, $data['role']);

        return response()->json([
            'message' => $mensaje,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre ?? $usuario->name,
                'correo' => $usuario->correo ?? $usuario->email,
                'role' => $data['role'],
            ]
        ], 201);
    }

    /**
     * Login universal
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required'
        ]);

        $usuario = null;
        $tipoUsuario = null;

        // Estudiante
        $estudiante = Estudiante::where('correo', $data['correo'])->first();
        if ($estudiante && Hash::check($data['contrasena'], $estudiante->contrasena)) {
            $usuario = $estudiante;
            $tipoUsuario = 'estudiante';
        }

        // Maestro
        if (!$usuario) {
            $maestro = Maestro::where('correo', $data['correo'])->first();
            if ($maestro && Hash::check($data['contrasena'], $maestro->contrasena)) {
                $usuario = $maestro;
                $tipoUsuario = 'maestro';
            }
        }

        // Admin
        if (!$usuario) {
            $admin = User::where('email', $data['correo'])->first();
            if ($admin && Hash::check($data['contrasena'], $admin->password)) {
                $usuario = $admin;
                $tipoUsuario = 'admin';
            }
        }

        if (!$usuario) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        // JWT
        $token = $this->generateToken($usuario, $tipoUsuario);

        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre ?? $usuario->name,
                'correo' => $usuario->correo ?? $usuario->email,
                'role' => $tipoUsuario,
                'especialidad' => $usuario->especialidad ?? null
            ]
        ]);
    }

    /**
     * ME — devuelve usuario según token
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $role = $request->get('auth_role');

        // Responder con todos los datos relevantes según el rol
        if ($role === 'maestro') {
            return response()->json([
                'id' => $user->id,
                'nombre' => $user->nombre,
                'correo' => $user->correo,
                'role' => 'maestro',
                'especialidad' => $user->especialidad ?? null,
                'biografia' => $user->biografia ?? null,
                'telefono' => $user->telefono ?? null
            ]);
        } elseif ($role === 'estudiante') {
            return response()->json([
                'id' => $user->id,
                'nombre' => $user->nombre,
                'correo' => $user->correo,
                'role' => 'estudiante',
                'carnet' => $user->carnet ?? null,
                'telefono' => $user->telefono ?? null,
                'carrera' => $user->carrera ?? null
            ]);
        } else { // admin u otro
            return response()->json([
                'id' => $user->id,
                'nombre' => $user->name,
                'correo' => $user->email,
                'role' => $user->role ?? 'admin'
            ]);
        }
    }

    /**
     * Generar JWT REAL
     */
    private function generateToken($user, $role)
    {
        $payload = [
            'user_id' => $user->id,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + (60 * 60), // 1 hora
        ];

        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }

    /**
     * Validar JWT REAL (lo usa el middleware)
     */
    public static function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            return (array)$decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
}
