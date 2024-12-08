<?php

namespace Database\Factories;

use App\Models\InvoiceTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceTemplateFactory extends Factory
{
    protected $model = InvoiceTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => $this->faker->regexify('[a-f0-9]{24}'), // Simula un ObjectId de MongoDB
            'template_name' => $this->faker->word,
            'column_mappings' => [
                'amount' => 'total',
                'date' => 'invoice_date',
            ],
            'formulas' => [
                ['field' => 'discount', 'formula' => 'amount * 0.10'],
            ],
            'validation_rules' => [
                ['field' => 'amount', 'rule' => 'required|numeric|min:0'],
            ],
            'aggregations' => [
                ['field' => 'amount', 'type' => 'sum'],
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
