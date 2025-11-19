<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use App\Models\Estudiante;
use Illuminate\Http\Request;

class CertificadoController extends Controller
{
    /**
     * Mostrar un certificado específico
     */
    public function show($id)
    {
        $certificado = Certificado::with([
            'inscripcion.estudiante',
            'inscripcion.curso',
        ])->find($id);

        if (!$certificado) {
            return response()->json(['error' => 'Certificado no encontrado'], 404);
        }

        return response()->json([
            'id' => $certificado->id,
            'codigo' => $certificado->codigo,
            'url_qr' => $certificado->url_qr,
            'fecha_emision' => $certificado->fecha_emision,
            'estudiante' => $certificado->inscripcion->estudiante->only(['id', 'nombre', 'correo']),
            'curso' => [
                'id' => $certificado->inscripcion->curso->id,
                'titulo' => $certificado->inscripcion->curso->titulo,
                'costo_centavos' => $certificado->inscripcion->curso->costo_centavos,
            ],
        ]);
    }

    /**
     * Obtener certificados del estudiante autenticado
     */
    public function misCertificados(Request $request)
    {
        $user = $request->user();
        
        if (!($user instanceof Estudiante)) {
            return response()->json(['error' => 'Solo estudiantes pueden acceder a esta función'], 403);
        }

        $certificados = $user->certificados()->with([
            'inscripcion.curso'
        ])->get();

        return response()->json($certificados->map(function ($cert) {
            return [
                'id' => $cert->id,
                'codigo' => $cert->codigo,
                'url_qr' => $cert->url_qr,
                'fecha_emision' => $cert->fecha_emision,
                'curso' => [
                    'id' => $cert->inscripcion->curso->id,
                    'titulo' => $cert->inscripcion->curso->titulo,
                    'descripcion' => $cert->inscripcion->curso->descripcion,
                ]
            ];
        }));
    }
}
