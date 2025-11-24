<?php
 
namespace App\Http\Controllers;
 
use App\Models\Inscripcion;
use App\Models\Certificado;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
 
class CertificadoController extends Controller
{
    public function descargar($inscripcionId)
    {
        $inscripcion = Inscripcion::with('estudiante', 'curso')->findOrFail($inscripcionId);
 
        // Crear o recuperar certificado existente
        $cert = Certificado::firstOrCreate(
            ['inscripcion_id' => $inscripcionId],
            [
                'codigo' => strtoupper(Str::random(10)),
                'fecha_emision' => now()
            ]
        );
 
        // Convertir fecha_emision a Carbon
        $fechaEmision = Carbon::parse($cert->fecha_emision);
 
        // Datos para la vista PDF
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