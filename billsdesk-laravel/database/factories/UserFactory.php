<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\Company;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // puedes usar la función Hash::make si prefieres
            'remember_token' => Str::random(10),
            'phone' => $this->faker->phoneNumber,
            'Address' => $this->faker->address,
            'role_id' => Role::factory(), // Relación con Role
            'company_id' => Company::factory(), // Relación con Company
        ];
    }

    /**
     * Indicate that the user is unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
