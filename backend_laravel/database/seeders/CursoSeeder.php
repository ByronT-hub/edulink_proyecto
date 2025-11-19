<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Curso;

class CursoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cursos = [
            [
                'titulo' => 'Desarrollo Web con Laravel',
                'descripcion' => 'Aprende a desarrollar aplicaciones web modernas con Laravel',
                'precio' => 2999.00,
                'duracion' => 60,
                'categoria' => 'Desarrollo Web',
                'nivel' => 'Intermedio',
                'requisitos' => 'Conocimientos básicos de PHP',
                'maestro_id' => 1,
                'activo' => true,
            ],
            [
                'titulo' => 'React para Principiantes',
                'descripcion' => 'Domina React y crea interfaces de usuario interactivas',
                'precio' => 1999.00,
                'duracion' => 40,
                'categoria' => 'Frontend',
                'nivel' => 'Principiante',
                'requisitos' => 'Conocimientos básicos de JavaScript',
                'maestro_id' => 1,
                'activo' => true,
            ],
            [
                'titulo' => 'Base de Datos MySQL',
                'descripcion' => 'Aprende a diseñar y administrar bases de datos MySQL',
                'precio' => 1499.00,
                'duracion' => 30,
                'categoria' => 'Bases de Datos',
                'nivel' => 'Intermedio',
                'requisitos' => 'Conceptos básicos de programación',
                'maestro_id' => 1,
                'activo' => true,
            ],
            [
                'titulo' => 'Python para Ciencia de Datos',
                'descripcion' => 'Introducción a Python aplicado a análisis de datos',
                'precio' => 2499.00,
                'duracion' => 50,
                'categoria' => 'Data Science',
                'nivel' => 'Avanzado',
                'requisitos' => 'Conocimientos de Python y matemáticas',
                'maestro_id' => 1,
                'activo' => true,
            ],
            [
                'titulo' => 'Diseño UX/UI',
                'descripcion' => 'Principios de diseño de experiencia e interfaz de usuario',
                'precio' => 1799.00,
                'duracion' => 35,
                'categoria' => 'Diseño',
                'nivel' => 'Principiante',
                'requisitos' => 'Ninguno',
                'maestro_id' => 1,
                'activo' => false, // Curso inactivo para pruebas
            ],
        ];

        foreach ($cursos as $curso) {
            Curso::create($curso);
        }

        $this->command->info('Cursos de prueba creados exitosamente');
    }
}
