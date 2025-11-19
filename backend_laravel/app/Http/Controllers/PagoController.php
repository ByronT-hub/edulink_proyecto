<?php 

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Certificado;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PagoController extends Controller
{
    /**
     * Autorizar pago y generar inscripción automáticamente
     */
    public function autorizar(Request $request)
    {
        $data = $request->validate([
            'curso_id'        => 'required|exists:cursos,id',
            'tarjeta'         => 'required|array',
            'tarjeta.nombre'  => 'required|string',
            'tarjeta.pan'     => 'required|string',
            'tarjeta.exp_mm'  => 'required|string',
            'tarjeta.exp_yy'  => 'required|string',
            'tarjeta.ccv'     => 'required|string',
        ]);

        // Obtener estudiante autenticado (middleware simpleauth)
        $estudiante = $request->auth_user;

        if (!$estudiante || $request->auth_role !== 'estudiante') {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        // Verificar curso
        $curso = Curso::findOrFail($data['curso_id']);

        // Verificar que no esté inscrito ya
        $yaInscrito = Inscripcion::where('estudiante_id', $estudiante->id)
                                ->where('curso_id', $curso->id)
                                ->exists();

        if ($yaInscrito) {
            return response()->json(['error' => 'Ya estás inscrito en este curso'], 409);
        }

        // --------------------------------------
        // Preparar payload EXACTO para Flask
        // --------------------------------------
        $payloadFlask = [
            'merchant_ref'   => "curso-{$curso->id}-est-{$estudiante->id}",
            'amount_cents'   => intval($curso->precio * 100),
            'currency'       => "GTQ",
            'card'           => [
                "holder_name" => $data["tarjeta"]["nombre"],
                "pan"         => $data["tarjeta"]["pan"],
                "exp_mm"      => $data["tarjeta"]["exp_mm"],
                "exp_yy"      => $data["tarjeta"]["exp_yy"],
                "ccv"         => $data["tarjeta"]["ccv"],
            ]
        ];

        // --------------------------------------
        // Enviar al microservicio Flask
        // --------------------------------------
        $url = env("FLASK_URL", "http://127.0.0.1:5055") . "/api/tarjetas/autorizar";

        $response = Http::post($url, $payloadFlask);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Error comunicando con el microservicio de pagos',
                'detalle' => $response->body()
            ], 502);
        }

        $body = $response->json();

        $approved = $body['approved'] ?? false;
        $authCode = $body['auth_code'] ?? null;
        $message  = $body['message'] ?? 'Error inesperado';

        // --------------------------------------
        // 1. Crear inscripción
        // --------------------------------------
        $inscripcion = Inscripcion::create([
            'estudiante_id' => $estudiante->id,
            'curso_id'      => $curso->id,
            'estado'        => $approved ? 'pagado' : 'pendiente',
        ]);

        // --------------------------------------
        // 2. Registrar pago
        // --------------------------------------
        $pago = Pago::create([
            'inscripcion_id'     => $inscripcion->id,
            'monto_centavos'     => intval($curso->precio * 100),
            'moneda'             => 'GTQ',
            'estado'             => $approved ? "approved" : "rejected",
            'codigo_autorizacion'=> $authCode,
            'mensaje'            => $message,
        ]);

        // --------------------------------------
        // 3. Generar certificado SOLO si aprobó
        // --------------------------------------
        $certificado = null;

        if ($approved) {
            $certificado = $this->generarCertificado($inscripcion);
        }

        return response()->json([
            'message'      => 'Pago procesado',
            'estado'       => $approved ? "approved" : "rejected",
            'pago'         => $pago,
            'inscripcion'  => $inscripcion->load('curso'),
            'certificado'  => $certificado,
        ]);
    }

    /**
     * Generar certificado si el pago fue aprobado
     */
    protected function generarCertificado(Inscripcion $inscripcion): Certificado
    {
        // Generar código único
        do {
            $codigo = 'CERT-' . Str::upper(Str::random(6));
        } while (Certificado::where('codigo', $codigo)->exists());

        // URL para validar en Flask
        $urlQr = env("FLASK_URL", "http://127.0.0.1:5055") . "/api/validar/" . $codigo;

        return Certificado::create([
            'inscripcion_id' => $inscripcion->id,
            'codigo'         => $codigo,
            'url_qr'         => $urlQr,
            'fecha_emision'  => Carbon::now(),
        ]);
    }

    public function misPagos(Request $request)
{
    $estudiante = $request->auth_user;

    if (!$estudiante || $request->auth_role !== 'estudiante') {
        return response()->json(['error' => 'No autorizado'], 401);
    }

    // Obtener todos los pagos del estudiante autenticado
    $pagos = Pago::whereHas('inscripcion', function ($q) use ($estudiante) {
        $q->where('estudiante_id', $estudiante->id);
    })
    ->with(['inscripcion.curso'])
    ->orderBy('created_at', 'desc')
    ->get();

    return response()->json([
        'total' => $pagos->count(),
        'pagos' => $pagos,
    ]);
}

}
