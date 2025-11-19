<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Estudiante;
use App\Models\Maestro;
use App\Models\User;
use App\Http\Controllers\AuthController;

class SimpleAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        $payload = AuthController::validateToken($token);

        if (!$payload) {
            return response()->json(['error' => 'Token inválido o expirado'], 401);
        }

        // Obtener usuario según el rol
        if ($payload['role'] === 'estudiante') {
            $user = Estudiante::find($payload['user_id']);
        } elseif ($payload['role'] === 'maestro') {
            $user = Maestro::find($payload['user_id']);
        } else {
            $user = User::find($payload['user_id']);
        }

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // 👍 Guardar usuario y rol para los controladores
        $request->merge([
            'auth_user' => $user,
            'auth_role' => $payload['role'],
        ]);

        // Laravel también podrá hacer $request->user()
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
