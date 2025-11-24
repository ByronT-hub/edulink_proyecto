<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CertificadoController extends Controller
{
    // ===============================
    // 1. Obtener mis certificados
    // ===============================
    public function misCertificados(Request $request)
    {
        $userId = $request->user()->id;

        $certificados = Certificado::with(['inscripcion.curso', 'inscripcion.estudiante'])
            ->whereHas('inscripcion', function ($q) use ($userId) {
                $q->where('estudiante_id', $userId);
            })
            ->get();

        return response()->json($certificados);
    }

    // ===============================
    // 2. Obtener certificado por ID
    // ===============================
    public function show($id)
    {
        $cert = Certificado::with(['inscripcion.curso', 'inscripcion.estudiante'])
            ->findOrFail($id);

        return response()->json($cert);
    }

    // ===============================
    // 3. Descargar certificado
    // ===============================
    public function descargar($inscripcionId)
    {
        $inscripcion = Inscripcion::with('estudiante', 'curso')->findOrFail($inscripcionId);

        // Crear o recuperar certificado existente
        $cert = Certificado::firstOrCreate(
            ['inscripcion_id' => $inscripcionId],
            [
                'codigo' => strtoupper(Str::random(10)),
                'url_qr' => '/api/validar/' . strtoupper(Str::random(10)),
                'fecha_emision' => now()
            ]
        );

        $fechaEmision = Carbon::parse($cert->fecha_emision);

        $data = [
            'nombre' => $inscripcion->estudiante->nombre,
            'curso' => $inscripcion->curso->titulo,
            'fecha' => $fechaEmision->format('d/m/Y'),
            'codigo' => $cert->codigo,
        ];

        $pdf = Pdf::loadView('certificados.certificado', $data);

        return $pdf->download("certificado_{$inscripcionId}.pdf");
    }
}
