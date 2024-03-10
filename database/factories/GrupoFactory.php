<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grupo>
 */
class GrupoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = $this->faker->unique()->randomElement(['Fisioterapia', 'Neurología', 'Medicina General', 'Odontología']);
        return [
            'nombre' => $nombre,
            'slug' => Str::slug($nombre),
            // Puedes agregar más atributos aquí si es necesario
        ];
    }
}
