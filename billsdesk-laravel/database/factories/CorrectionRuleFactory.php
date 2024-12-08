<?php

namespace Database\Factories;

use App\Models\CorrectionRule;
use App\Models\InvoiceTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CorrectionRuleFactory extends Factory
{
    protected $model = CorrectionRule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => $this->faker->regexify('[a-f0-9]{24}'), // Simula un ObjectId de MongoDB
            'rule_name' => $this->faker->sentence(3),
            'conditions' => [
                ['field' => 'amount', 'operator' => '>', 'value' => 100],
            ],
            'corrections' => [
                ['field' => 'amount', 'action' => 'set', 'value' => 99],
            ],
            'template_id' => InvoiceTemplate::factory()->create()->_id, // Relación con InvoiceTemplate
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
