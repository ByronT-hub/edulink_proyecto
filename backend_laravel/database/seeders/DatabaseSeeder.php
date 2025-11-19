<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

// Modelos
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Certificado;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedEstudiantes();
        $this->seedCursos();
        $this->seedInscripcionesPagosCertificados();
    }

    protected function seedEstudiantes(): void
    {
        // Estudiantes fijos para la demo (puedes cambiar nombres/correos)
        $demoEstudiantes = [
            [
                'nombre' => 'Byron Estudiante',
                'correo' => 'byron@example.com',
            ],
            [
                'nombre' => 'Ana López',
                'correo' => 'ana@example.com',
            ],
            [
                'nombre' => 'Carlos Pérez',
                'correo' => 'carlos@example.com',
            ],
        ];

        foreach ($demoEstudiantes as $e) {
            Estudiante::create([
                'nombre'     => $e['nombre'],
                'correo'     => $e['correo'],
                'contrasena' => Hash::make('12345678'),
            ]);
        }

        // Estudiantes extra aleatorios
        Estudiante::factory()->count(7)->create(); // total ~10
    }

    protected function seedCursos(): void
    {
        // Cursos fijos para la demo
        $demoCursos = [
            [
                'titulo'         => 'Introducción a Programación',
                'descripcion'    => 'Curso básico para aprender lógica y programación.',
                'precio'         => 1500.00,
                'duracion'       => 30,
                'categoria'      => 'Programación',
                'nivel'          => 'Principiante',
                'requisitos'     => 'Ninguno',
                'maestro_id'     => 1,
                'activo'         => true,
            ],
            [
                'titulo'         => 'Redes de Computadoras',
                'descripcion'    => 'Fundamentos de redes, topologías, modelos OSI y TCP/IP.',
                'precio'         => 1800.00,
                'duracion'       => 40,
                'categoria'      => 'Redes',
                'nivel'          => 'Intermedio',
                'requisitos'     => 'Conocimientos básicos de computación',
                'maestro_id'     => 1,
                'activo'         => true,
            ],
            [
                'titulo'         => 'Desarrollo Web con Laravel',
                'descripcion'    => 'Curso práctico de desarrollo de APIs con Laravel.',
                'precio'         => 2000.00,
                'duracion'       => 50,
                'categoria'      => 'Desarrollo Web',
                'nivel'          => 'Avanzado',
                'requisitos'     => 'PHP básico y conceptos de MVC',
                'maestro_id'     => 1,
                'activo'         => true,
            ],
        ];

        foreach ($demoCursos as $c) {
            Curso::create($c);
        }

        // Cursos extra aleatorios
        Curso::factory()->count(5)->create();
    }

    protected function seedInscripcionesPagosCertificados(): void
    {
        $estudiantes = Estudiante::all();
        $cursos      = Curso::where('activo', true)->get();

        if ($estudiantes->isEmpty() || $cursos->isEmpty()) {
            return;
        }

        foreach ($estudiantes as $estudiante) {
            // Cada estudiante se inscribe a 1 o 2 cursos
            $cursosSeleccionados = $cursos->random(rand(1, min(2, $cursos->count())));

            foreach ($cursosSeleccionados as $curso) {
                // Crear inscripción inicial como pendiente
                $inscripcion = Inscripcion::create([
                    'estudiante_id' => $estudiante->id,
                    'curso_id'      => $curso->id,
                    'estado'        => 'pendiente',
                ]);

                // Decidimos al azar si esta inscripción ya está pagada o no
                $estaPagada = rand(0, 1) === 1;

                if ($estaPagada) {
                    $this->crearPagoYCertificado($inscripcion, $curso);
                }
            }
        }
    }

    protected function crearPagoYCertificado(Inscripcion $inscripcion, Curso $curso): void
    {
        // 1. Crear pago (aprobado)
        $pago = Pago::create([
            'inscripcion_id'     => $inscripcion->id,
            'monto_centavos'     => $curso->precio * 100, // Convertir precio a centavos
            'moneda'             => 'GTQ',
            'estado'             => 'approved',
            'codigo_autorizacion'=> 'AUTH-' . strtoupper(Str::random(6)),
            'mensaje'            => 'Pago simulado aprobado en seeder',
        ]);

        // 2. Actualizar estado de inscripción
        $inscripcion->estado = 'pagado';
        $inscripcion->save();

        // 3. Crear certificado
        do {
            $codigoCert = 'CERT-' . strtoupper(Str::random(6));
        } while (Certificado::where('codigo', $codigoCert)->exists());

        $urlQr = 'http://localhost:5055/api/validar/' . $codigoCert; // igual que en tu flujo de pagos

        $certificado = Certificado::create([
            'inscripcion_id' => $inscripcion->id,
            'codigo'         => $codigoCert,
            'url_qr'         => $urlQr,
            'fecha_emision'  => Carbon::now(),
        ]);
    }
}
