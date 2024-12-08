<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\InvoiceTemplate;
use App\Models\CorrectionRule;
use Illuminate\Support\Facades\Config;

class InvoiceTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configurar MongoDB como conexión por defecto
        Config::set('database.default', 'mongodb');
    }

    public function testCanCreateInvoiceTemplate(): void
    {
        $template = InvoiceTemplate::create([
            'company_id' => 1,
            'template_name' => 'Plantilla de Factura prueba',
            'column_mappings' => [
                'ship_id' => 'ship_id',
                'peso' => 'weight',
                'peso_2' => 'weight 2',
                'altura' => 'height',
                'ancho' => 'width',
                'largo' => 'lenght',
                'coste' => 'cost',
                'coste_extra' => 'extra cost',
                'cliente' => 'client',
                'direccion' => 'direction',
                'codigo_postal' => 'cp',
                'pais' => 'country',
                'devuelto' => 'returned',
            ],
            'formulas' => [
                [
                    'new_column' => 'real_weight',
                    'formula' => '(lenght * width * height * 167) / 1000000',
                ],
            ],
            'validation_rules' => [
                [
                    'field' => 'country',
                    'operator' => '==',
                    'value' => 'España',
                    'conditions' => [
                        [
                            'field' => 'lenght',
                            'operator' => '>',
                            'value' => 120,
                            'highlight' => 'red',
                        ],
                    ],
                ],
                [
                    'field' => 'real_weight',
                    'operator' => '>',
                    'value' => 120,
                    'row_highlight' => '#9f3',
                ],
            ],
            'aggregations' => [
                [
                    'type' => 'sum',
                    'fields' => ['cost', 'real_weight'],
                ],
            ],
        ]);

        $this->assertNotNull($template->_id);
        $this->assertEquals('Plantilla de Factura prueba', $template->template_name);
        $this->assertArrayHasKey('ship_id', $template->column_mappings);
        $this->assertEquals('(lenght * width * height * 167) / 1000000', $template->formulas[0]['formula']);
        $this->assertEquals('country', $template->validation_rules[0]['field']);
        $this->assertEquals('sum', $template->aggregations[0]['type']);
    }

    public function testInvoiceTemplateHasManyCorrectionRules(): void
    {
        $template = InvoiceTemplate::factory()
            ->has(CorrectionRule::factory()->count(3), 'correctionRules')
            ->create();

        $this->assertCount(3, $template->correctionRules);
        $this->assertInstanceOf(CorrectionRule::class, $template->correctionRules->first());
    }

    public function testInvoiceTemplateCanRetrieveComplexValidationRules(): void
    {
        $template = InvoiceTemplate::create([
            'company_id' => 1,
            'template_name' => 'Plantilla Compleja',
            'validation_rules' => [
                [
                    'field' => 'country',
                    'operator' => '==',
                    'value' => 'España',
                    'conditions' => [
                        [
                            'field' => 'weight',
                            'operator' => '>',
                            'value' => 50,
                            'highlight' => '#09f',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals('country', $template->validation_rules[0]['field']);
        $this->assertEquals('#09f', $template->validation_rules[0]['conditions'][0]['highlight']);
    }

    public function testInvoiceTemplateCanHandleAggregations(): void
    {
        $template = InvoiceTemplate::create([
            'company_id' => 1,
            'template_name' => 'Plantilla con Agregaciones',
            'aggregations' => [
                [
                    'type' => 'sum',
                    'fields' => ['cost', 'real_weight'],
                ],
            ],
        ]);

        $this->assertEquals('sum', $template->aggregations[0]['type']);
        $this->assertEquals(['cost', 'real_weight'], $template->aggregations[0]['fields']);
    }
}
