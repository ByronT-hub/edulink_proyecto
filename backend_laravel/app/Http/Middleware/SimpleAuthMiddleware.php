<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Models\Estudiante;
use App\Models\Maestro;
use App\Models\User;

class SimpleAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1) OBTENER TOKEN
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token no enviado'], 401);
        }

        // 2) VALIDAR TOKEN
        $payload = AuthController::validateToken($token);

        if (!$payload) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        // 3) OBTENER EL USUARIO SEGÚN EL ROL
        $role = $payload['role'] ?? null;
        $user = null;

        if ($role === 'estudiante') {
            $user = Estudiante::find($payload['user_id']);
        } elseif ($role === 'maestro') {
            $user = Maestro::find($payload['user_id']);
        } else {
            $user = User::find($payload['user_id']);
        }

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // 4) PASAR DATOS AL REQUEST → NECESARIO PARA PagoController
        $request->merge([
            'auth_user' => $user,
            'auth_role' => $role,
        ]);

        // Configurar user() para Laravel
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
