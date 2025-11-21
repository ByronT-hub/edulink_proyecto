<?php
namespace App\Http\Controllers;

use App\Models\Progreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgresoController extends Controller
{
    // Obtener progreso de un estudiante en un curso
    public function show($inscripcionId)
    {
        $progreso = Progreso::where('inscripcion_id', $inscripcionId)->first();
        if (!$progreso) {
            return response()->json(['progreso' => null], 200);
        }
        return response()->json(['progreso' => $progreso], 200);
    }

    // Guardar o actualizar progreso
    public function update(Request $request, $inscripcionId)
    {
        $data = $request->validate([
            'lecciones_completadas' => 'required|array',
            'porcentaje' => 'required|integer|min:0|max:100',
        ]);

        $progreso = Progreso::updateOrCreate(
            ['inscripcion_id' => $inscripcionId],
            [
                'lecciones_completadas' => $data['lecciones_completadas'],
                'porcentaje' => $data['porcentaje'],
            ]
        );
        return response()->json(['progreso' => $progreso], 200);
    }
}
