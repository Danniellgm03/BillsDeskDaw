<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'permissions' => $this->faker->randomElements(
                ['create', 'read', 'update', 'delete'],
                $this->faker->numberBetween(1, 4)
            ), // Genera un subconjunto de permisos
            'isAdmin' => $this->faker->boolean, // Genera true o false
        ];
    }
}
