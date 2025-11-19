<?php

namespace Database\Factories;

use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estudiante>
 */
class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    public function definition(): array
    {
        return [
            'nombre'     => $this->faker->name(),
            'correo'     => $this->faker->unique()->safeEmail(),
            // usamos una misma contraseña para todos los seeds (para pruebas)
            'contrasena' => Hash::make('12345678'),
        ];
    }
}
