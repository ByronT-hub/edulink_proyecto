<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estudiante;
use Illuminate\Support\Facades\Hash;

class EstudiantesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estudiantes = [
            [
                'nombre' => 'Ana García',
                'correo' => 'ana.garcia@email.com',
                'contrasena' => Hash::make('123456'),
                'carnet' => '2024-12345',
                'telefono' => '5555-1234',
                'carrera' => 'Ingeniería de Sistemas',
                'universidad' => 'Universidad de San Carlos',
                'nivel_estudio' => 'Licenciatura',
                'intereses' => 'Programación, Desarrollo Web, Bases de Datos',
            ],
            [
                'nombre' => 'Carlos Mendoza',
                'correo' => 'carlos.mendoza@email.com',
                'contrasena' => Hash::make('123456'),
                'carnet' => '2024-67890',
                'telefono' => '5555-5678',
                'carrera' => 'Administración de Empresas',
                'universidad' => 'Universidad Rafael Landívar',
                'nivel_estudio' => 'Maestría',
                'intereses' => 'Marketing Digital, E-commerce, Emprendimiento',
            ],
            [
                'nombre' => 'María López',
                'correo' => 'maria.lopez@email.com',
                'contrasena' => Hash::make('123456'),
                'carnet' => '2024-11111',
                'telefono' => '5555-9999',
                'carrera' => 'Diseño Gráfico',
                'universidad' => 'Universidad Francisco Marroquín',
                'nivel_estudio' => 'Licenciatura',
                'intereses' => 'UI/UX Design, Ilustración Digital, Branding',
            ],
            [
                'nombre' => 'Roberto Silva',
                'correo' => 'roberto.silva@email.com',
                'contrasena' => Hash::make('123456'),
                'carnet' => '2024-22222',
                'telefono' => '5555-1111',
                'carrera' => 'Ingeniería Civil',
                'universidad' => 'Universidad de San Carlos',
                'nivel_estudio' => 'Licenciatura',
                'intereses' => 'Construcción, Proyectos, AutoCAD',
            ],
            [
                'nombre' => 'Lucía Fernández',
                'correo' => 'lucia.fernandez@email.com',
                'contrasena' => Hash::make('123456'),
                'carnet' => '2024-33333',
                'telefono' => '5555-2222',
                'carrera' => 'Psicología',
                'universidad' => 'Universidad Rafael Landívar',
                'nivel_estudio' => 'Maestría',
                'intereses' => 'Psicología Clínica, Terapia, Investigación',
            ]
        ];

        foreach ($estudiantes as $estudiante) {
            Estudiante::create($estudiante);
        }

        $this->command->info('Estudiantes de prueba creados exitosamente!');
    }
}