<?php
namespace App\Http\Controllers;

use App\Models\Progreso;
use Illuminate\Http\Request;

class ProgresoController extends Controller
{
    // Obtener progreso
    public function show($inscripcionId)
    {
        $progreso = Progreso::where('inscripcion_id', $inscripcionId)->first();

        return response()->json([
            'progreso' => $progreso
        ], 200);
    }

    // Guardar o actualizar progreso
    public function update(Request $request, $inscripcionId)
    {
        $data = $request->validate([
            'lecciones_completadas' => 'array',   // YA NO ES REQUIRED
            'porcentaje' => 'required|integer|min:0|max:100',
        ]);

        $progreso = Progreso::updateOrCreate(
            ['inscripcion_id' => $inscripcionId],
            [
                'lecciones_completadas' => $data['lecciones_completadas'] ?? [],
                'porcentaje' => $data['porcentaje'],
            ]
        );

        return response()->json([
            'progreso' => $progreso
        ], 200);
    }
}
