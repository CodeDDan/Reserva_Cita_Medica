<?php

namespace Database\Factories;

use App\Models\Grupo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empleado>
 */
class EmpleadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //'grupo_id' => $idTraidoDeGrupos, // Asigna un grupo aleatorio usando la factoría de Grupo
            'nombre' => $this->faker->firstName,
            'apellido' => $this->faker->lastName,
            'edad' => $this->faker->numberBetween(18, 65),
            'password' => bcrypt('1234'), // Se puede ajustar la lógica de generación de contraseñas
            'correo' => $this->faker->unique()->safeEmail,
            'direccion' => $this->faker->address,
            'telefono' => $this->faker->unique()->phoneNumber,
            'fecha_de_contratacion' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'contacto_opcional' => $this->faker->optional()->phoneNumber,
            'activo' => $this->faker->boolean(90), // 90% de probabilidad de estar activo
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
