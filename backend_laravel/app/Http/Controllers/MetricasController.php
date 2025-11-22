<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Maestro;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Pago;

class MetricasController extends Controller
{
    public function cursos()
    {
        $response = Http::get('http://localhost:3000/api/metricas/cursos');

        if (!$response->successful()) {
            return response()->json([
                'error' => 'No se pudo obtener métricas de Rails',
            ], 502);
        }

        return response()->json($response->json());
    }
    // NUEVO: Resumen de métricas sencillas
    public function resumen()
    {
        $total_usuarios = User::count();
        $total_maestros = Maestro::count();
        $total_estudiantes = Estudiante::count();
        $total_cursos = Curso::count();
        $total_inscripciones = Inscripcion::count();
        $total_pagos = Pago::count();
        $monto_total = Pago::sum('monto_centavos') / 100.0;
        $cursos_activos = Curso::where('activo', true)->count();
        $ultimo_usuario = User::orderBy('created_at', 'desc')->first();
        $ultimo_curso = Curso::orderBy('created_at', 'desc')->first();

        return response()->json([
            'total_usuarios' => $total_usuarios,
            'total_maestros' => $total_maestros,
            'total_estudiantes' => $total_estudiantes,
            'total_cursos' => $total_cursos,
            'total_inscripciones' => $total_inscripciones,
            'total_pagos' => $total_pagos,
            'monto_total' => $monto_total,
            'cursos_activos' => $cursos_activos,
            'ultimo_usuario' => $ultimo_usuario ? $ultimo_usuario->name . ' (' . $ultimo_usuario->created_at . ')' : null,
            'ultimo_curso' => $ultimo_curso ? $ultimo_curso->titulo . ' (' . $ultimo_curso->created_at . ')' : null,
        ]);
    }
}
