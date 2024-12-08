<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'file_type' => $this->faker->randomElement(['invoice', 'other']),
            'file_path' => $this->faker->filePath(), // Ruta simulada
            'file_name' => $this->faker->word . '.' . $this->faker->fileExtension,
            'file_extension' => $this->faker->fileExtension,
            'file_size' => $this->faker->numberBetween(100, 5000), // Tamaño en KB
            'file_size_type' => $this->faker->randomElement(['KB', 'MB']),
            'file_mime_type' => $this->faker->mimeType,
            'file_description' => $this->faker->sentence,
            'file_status' => $this->faker->randomElement(['active', 'inactive']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'deleted_by' => User::factory(),
            'is_fav' => false,
        ];
    }
}
