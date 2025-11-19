<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Pago;
use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    /**
     * Listar inscripciones del usuario autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user instanceof Estudiante) {
            $inscripciones = $user->inscripciones()->with('curso')->get();
        } else {
            // Admin ve todas
            $inscripciones = Inscripcion::with(['estudiante', 'curso'])->get();
        }
        
        return response()->json($inscripciones);
    }

    /**
     * 🔥 ESTE MÉTODO YA NO INSCRIBE SIN PAGAR
     * AHORA SOLO MUESTRA UN MENSAJE CORRECTO
     * PARA QUE EL FRONT USE PagoController
     */
    public function inscribirse(Request $request, $cursoId)
    {
        return response()->json([
            'error' => 'Las inscripciones deben hacerse mediante el endpoint /pagos/autorizar',
            'usar_endpoint' => '/api/pagos/autorizar',
            'ejemplo' => [
                'curso_id' => intval($cursoId),
                'tarjeta' => [
                    'nombre' => 'Juan Pérez',
                    'pan' => '4111111111111111',
                    'exp_mm' => '12',
                    'exp_yy' => '25',
                    'ccv' => '123'
                ]
            ]
        ], 400);
    }

    /**
     * Cursos inscritos del estudiante autenticado
     */
    public function misCursos(Request $request)
    {
        $estudiante = $request->auth_user;

        if (!$estudiante || $request->auth_role !== 'estudiante') {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $inscripciones = Inscripcion::where('estudiante_id', $estudiante->id)
                                   ->with(['curso.maestro', 'pagos'])
                                   ->get();

        return response()->json([
            'inscripciones' => $inscripciones
        ]);
    }

    /**
     * Admin crea inscripciones manuales
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
        ]);

        // Si es estudiante, usar su propio ID
        if ($user instanceof Estudiante) {
            $estudianteId = $user->id;
        } else {
            // Admin debe enviar estudiante_id
            $data = $request->validate([
                'estudiante_id' => 'required|exists:estudiantes,id',
                'curso_id' => 'required|exists:cursos,id',
            ]);
            $estudianteId = $data['estudiante_id'];
        }

        // Verificar que no exista inscripción previa
        $existe = Inscripcion::where('estudiante_id', $estudianteId)
                            ->where('curso_id', $data['curso_id'])
                            ->exists();
                            
        if ($existe) {
            return response()->json([
                'error' => 'Ya existe una inscripción para este curso'
            ], 409);
        }

        $inscripcion = Inscripcion::create([
            'estudiante_id' => $estudianteId,
            'curso_id' => $data['curso_id'],
            'estado' => 'pendiente',
        ]);

        $inscripcion->load(['estudiante', 'curso']);

        return response()->json($inscripcion, 201);
    }

    /**
     * Reportes administradores
     */
    public function reportes(Request $request)
    {
        $inscripciones = Inscripcion::with(['estudiante', 'curso'])
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->get();

        $inscripcionesPorCurso = Inscripcion::with('curso')
            ->selectRaw('curso_id, COUNT(*) as total_inscripciones')
            ->groupBy('curso_id')
            ->get();

        return response()->json([
            'por_estado' => $inscripciones,
            'por_curso' => $inscripcionesPorCurso,
            'total' => Inscripcion::count()
        ]);
    }
}
