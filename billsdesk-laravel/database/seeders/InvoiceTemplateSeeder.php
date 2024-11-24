<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InvoiceTemplate;

class InvoiceTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InvoiceTemplate::create([
            'company_id' => '64872e2d5bfa4f1a8a3c9e2f',
            'template_name' => 'Plantilla de Facturas Empresa XYZ',
            'column_mappings' => [
                'peso' => 'weight',
                'total' => 'total_amount',
                'direccion' => 'address',
                'fecha' => 'date',
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
