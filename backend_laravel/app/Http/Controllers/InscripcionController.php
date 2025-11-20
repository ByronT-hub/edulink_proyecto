<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Curso;
use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->auth_role;
        $user = $request->auth_user;

        if ($role === 'estudiante') {
            return response()->json(
                $user->inscripciones()->with('curso')->get()
            );
        }

        return response()->json(
            Inscripcion::with(['estudiante', 'curso'])->get()
        );
    }

    public function misCursos(Request $request)
    {
        $estudiante = $request->auth_user;
        $role = $request->auth_role;

        if (!$estudiante || $role !== 'estudiante') {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $inscripciones = Inscripcion::where('estudiante_id', $estudiante->id)
            ->with([
                'curso.maestro:id,nombre',
                'pagos:id,inscripcion_id,monto_centavos,estado,created_at'
            ])
            ->get();

        return response()->json([
            'inscripciones' => $inscripciones
        ]);
    }

    public function store(Request $request)
    {
        $role = $request->auth_role;
        $user = $request->auth_user;

        $data = $request->validate([
            'curso_id' => 'required|exists:cursos,id'
        ]);

        if ($role === 'estudiante') {
            $estudianteId = $user->id;
        } else {
            $data = $request->validate([
                'estudiante_id' => 'required|exists:estudiantes,id',
                'curso_id'      => 'required|exists:cursos,id',
            ]);

            $estudianteId = $data['estudiante_id'];
        }

        if (Inscripcion::where('estudiante_id', $estudianteId)
            ->where('curso_id', $data['curso_id'])
            ->exists()) 
        {
            return response()->json(['error' => 'Ya existe una inscripción'], 409);
        }

        $inscripcion = Inscripcion::create([
            'estudiante_id' => $estudianteId,
            'curso_id'      => $data['curso_id'],
            'estado'        => 'pendiente'
        ]);

        return response()->json(
            $inscripcion->load(['estudiante', 'curso']),
            201
        );
    }
}
