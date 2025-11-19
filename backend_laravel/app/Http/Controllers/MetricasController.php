<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

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
}
