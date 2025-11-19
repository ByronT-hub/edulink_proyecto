<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\Maestro;

class CursosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maestro = Maestro::first();
        
        if (!$maestro) {
            $this->command->error('No hay maestros disponibles. Crea un maestro primero.');
            return;
        }

        $cursos = [
            [
                'titulo' => 'Desarrollo Web con HTML y CSS',
                'descripcion' => 'Aprende a crear páginas web modernas con HTML5 y CSS3. Este curso te llevará desde lo básico hasta técnicas avanzadas de diseño web.',
                'precio' => 150.00,
                'duracion' => '8 semanas',
                'categoria' => 'Tecnología',
                'nivel' => 'Principiante',
                'requisitos' => 'Conocimientos básicos de computación',
                'maestro_id' => $maestro->id
            ],
            [
                'titulo' => 'JavaScript Moderno ES6+',
                'descripcion' => 'Domina JavaScript moderno y sus características más recientes. Aprende programación funcional, async/await, módulos y más.',
                'precio' => 250.00,
                'duracion' => '12 semanas',
                'categoria' => 'Programación',
                'nivel' => 'Intermedio',
                'requisitos' => 'HTML y CSS básico',
                'maestro_id' => $maestro->id
            ],
            [
                'titulo' => 'Diseño UX/UI Profesional',
                'descripcion' => 'Aprende diseño de experiencia e interfaz de usuario desde cero. Incluye teoría del color, tipografía y herramientas profesionales.',
                'precio' => 180.00,
                'duracion' => '10 semanas',
                'categoria' => 'Diseño',
                'nivel' => 'Principiante',
                'requisitos' => 'Ninguno',
                'maestro_id' => $maestro->id
            ],
            [
                'titulo' => 'Base de Datos con MySQL',
                'descripcion' => 'Aprende a diseñar, crear y gestionar bases de datos relacionales con MySQL. Incluye consultas avanzadas y optimización.',
                'precio' => 200.00,
                'duracion' => '6 semanas',
                'categoria' => 'Tecnología',
                'nivel' => 'Intermedio',
                'requisitos' => 'Lógica de programación básica',
                'maestro_id' => $maestro->id
            ],
            [
                'titulo' => 'Marketing Digital para Emprendedores',
                'descripcion' => 'Estrategias completas de marketing digital: SEO, SEM, redes sociales, email marketing y analítica web.',
                'precio' => 120.00,
                'duracion' => '8 semanas',
                'categoria' => 'Marketing',
                'nivel' => 'Principiante',
                'requisitos' => 'Conocimientos básicos de internet',
                'maestro_id' => $maestro->id
            ]
        ];

        foreach ($cursos as $curso) {
            Curso::create($curso);
        }

        $this->command->info('Cursos de prueba creados exitosamente!');
    }
}