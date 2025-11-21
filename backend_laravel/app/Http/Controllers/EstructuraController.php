<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estructura;

class EstructuraController extends Controller
{
    // Obtener la estructura de módulos/lecciones de un curso
    public function show($cursoId)
    {
        $estructura = Estructura::where('curso_id', $cursoId)->first();
        return response()->json([
            'estructura' => $estructura ? $estructura->estructura : [],
        ]);
    }

    // Guardar o actualizar la estructura de módulos/lecciones de un curso
    public function storeOrUpdate(Request $request, $cursoId)
    {
        $data = $request->validate([
            'estructura' => 'required|array',
        ]);
        $estructura = Estructura::updateOrCreate(
            ['curso_id' => $cursoId],
            ['estructura' => $data['estructura']]
        );
        return response()->json([
            'message' => 'Estructura guardada',
            'estructura' => $estructura->estructura,
        ]);
    }
}
