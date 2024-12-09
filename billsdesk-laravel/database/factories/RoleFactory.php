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
        $permissions = ['create', 'read', 'update', 'delete'];

        return [
            'name' => ['admin', 'user'][array_rand(['admin', 'user'])], // Alternativa a randomElement
            'permissions' => collect($permissions)
                ->random($this->faker->numberBetween(1, 4))
                ->toArray(), // Genera un subconjunto de permisos
            'isAdmin' => false, // Genera true o false
        ];
    }
}
