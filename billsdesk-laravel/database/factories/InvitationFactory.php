<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'token' => Str::random(40), // Token único
            'role_id' => Role::factory(), // Relación con Role
            'company_id' => Company::factory(), // Relación con Company
            'accepted' => $this->faker->boolean, // Genera true o false aleatoriamente
        ];
    }
}
