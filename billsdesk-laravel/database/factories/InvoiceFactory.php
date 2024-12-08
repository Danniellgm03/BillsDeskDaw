<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Company;
use App\Models\User;
use App\Models\File;
use App\Models\Contact;
use App\Models\InvoiceTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'file_id' => File::factory(),
            'status' =>  'pending',
            'date_to_pay' => $this->faker->dateTimeBetween('now', '+1 year'),
            'name_invoice' => $this->faker->unique()->word,
            'template_id' => $this->faker->regexify('[a-f0-9]{24}'),
            'contact_id' => Contact::factory(),
        ];
    }
}
