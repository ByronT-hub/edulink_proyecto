<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Estudiante;
use App\Models\Maestro;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Token no proporcionado'], 401);
        }
        
        // Usar exactamente el mismo sistema que AuthController
        $payload = \App\Http\Controllers\AuthController::validateToken($token);
        
        if (!$payload) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }
        
        // Verificar que el usuario tenga uno de los roles permitidos
        if (!in_array($payload['role'], $roles)) {
            return response()->json(['message' => 'No tienes permisos para acceder a este recurso'], 403);
        }
        
        // Obtener usuario según el rol usando solo los IDs del payload
        $usuario = null;
        
        if ($payload['role'] === 'estudiante') {
            $usuario = Estudiante::find($payload['user_id']);
        } elseif ($payload['role'] === 'maestro') {
            $usuario = Maestro::find($payload['user_id']);
        } elseif ($payload['role'] === 'admin') {
            $usuario = User::find($payload['user_id']);
        }
        
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        
        // Agregar información del usuario a la request
        $request->merge(['auth_user' => $usuario, 'auth_role' => $payload['role']]);
        
        return $next($request);
    }
}