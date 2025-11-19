<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Models\User;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
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
        
        // Verificar que sea un administrador
        if ($payload['role'] !== 'admin') {
            return response()->json([
                'error' => 'Acceso denegado. Se requieren permisos de administrador.'
            ], 403);
        }
        
        $user = User::find($payload['user_id']);
        
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Usuario administrador no encontrado'], 404);
        }
        
        // Agregar usuario a la request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        return $next($request);
    }
}