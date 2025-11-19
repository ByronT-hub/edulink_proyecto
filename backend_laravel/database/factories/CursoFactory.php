<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\EloquentFactories\Factory<\App\Models\Curso>
 */
class CursoFactory extends Factory
{
    protected $model = Curso::class;

    public function definition(): array
    {
        // fechas coherentes: inicio antes que fin
        $start = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $end   = (clone $start)->modify('+'.rand(15, 60).' days');

        return [
            'titulo'         => $this->faker->sentence(3),
            'descripcion'    => $this->faker->paragraph(),
            'costo_centavos' => $this->faker->numberBetween(50000, 300000), // Q500.00 a Q3000.00
            'fecha_inicio'   => $start->format('Y-m-d'),
            'fecha_fin'      => $end->format('Y-m-d'),
            'activo'         => $this->faker->boolean(80), // 80% activos
        ];
    }
}
