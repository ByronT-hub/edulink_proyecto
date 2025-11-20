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
    public function autorizar(Request $request)
    {
        // Validar payload
        $data = $request->validate([
            'curso_id'        => 'required|exists:cursos,id',
            'tarjeta'         => 'required|array',
            'tarjeta.nombre'  => 'required|string',
            'tarjeta.pan'     => 'required|string',
            'tarjeta.exp_mm'  => 'required|string',
            'tarjeta.exp_yy'  => 'required|string',
            'tarjeta.ccv'     => 'required|string',
        ]);

        // 🔥 Asegurar autenticación
        $estudiante = $request->auth_user;
        $role = $request->auth_role;

        if (!$estudiante || $role !== 'estudiante') {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        // Obtener curso
        $curso = Curso::findOrFail($data['curso_id']);

        // Evitar inscripciones duplicadas
        if (Inscripcion::where('estudiante_id', $estudiante->id)
            ->where('curso_id', $curso->id)
            ->exists())
        {
            return response()->json(['error' => 'Ya estás inscrito'], 409);
        }

        // Payload para Flask
        $payload = [
            'merchant_ref' => "curso-{$curso->id}-est-{$estudiante->id}",
            'amount_cents' => intval($curso->precio * 100),
            'currency'     => "GTQ",
            'card'         => [
                'holder_name' => $data['tarjeta']['nombre'],
                'pan'         => $data['tarjeta']['pan'],
                'exp_mm'      => $data['tarjeta']['exp_mm'],
                'exp_yy'      => $data['tarjeta']['exp_yy'],
                'ccv'         => $data['tarjeta']['ccv'],
            ]
        ];

        // URL del microservicio
        $url = env("FLASK_URL", "http://127.0.0.1:5055") . "/api/tarjetas/autorizar";

        try {
            $response = Http::timeout(10)->post($url, $payload);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Microservicio no responde',
                'detalle' => $e->getMessage()
            ], 500);
        }

        if (!$response->successful()) {
            return response()->json([
                'error'   => 'Error al comunicarse con microservicio',
                'detalle' => $response->body()
            ], 500);
        }

        $res = $response->json();
        $approved = $res['approved'] ?? false;
        $authCode = $res['auth_code'] ?? null;
        $mensaje  = $res['message'] ?? "Error desconocido";

        // Si fue rechazado
        if (!$approved) {

            $inscripcion = Inscripcion::create([
                'estudiante_id' => $estudiante->id,
                'curso_id'      => $curso->id,
                'estado'        => 'rechazado'
            ]);

            $pago = Pago::create([
                'inscripcion_id'      => $inscripcion->id,
                'monto_centavos'      => intval($curso->precio * 100),
                'moneda'              => 'GTQ',
                'estado'              => 'rejected',
                'codigo_autorizacion' => null,
                'mensaje'             => $mensaje,
            ]);

            return response()->json([
                'message'      => "Pago rechazado: $mensaje",
                'estado'       => 'rejected',
                'inscripcion'  => $inscripcion,
                'pago'         => $pago,
                'certificado'  => null
            ], 409);
        }

        // SI FUE APROBADO — INSCRIPCIÓN
        $inscripcion = Inscripcion::create([
            'estudiante_id' => $estudiante->id,
            'curso_id'      => $curso->id,
            'estado'        => 'pagado'
        ]);

        // REGISTRO DE PAGO
        $pago = Pago::create([
            'inscripcion_id'      => $inscripcion->id,
            'monto_centavos'      => intval($curso->precio * 100),
            'moneda'              => 'GTQ',
            'estado'              => 'approved',
            'codigo_autorizacion' => $authCode,
            'mensaje'             => $mensaje,
        ]);

        // CERTIFICADO
        $certificado = Certificado::create([
            'inscripcion_id' => $inscripcion->id,
            'codigo'         => 'CERT-' . Str::upper(Str::random(6)),
            'url_qr'         => env("FLASK_URL") . "/api/validar/" . $authCode,
            'fecha_emision'  => Carbon::now(),
        ]);

        return response()->json([
            'message'      => 'Pago aprobado',
            'estado'       => 'approved',
            'inscripcion'  => $inscripcion->load('curso'),
            'pago'         => $pago,
            'certificado'  => $certificado,
        ]);
    }
}
