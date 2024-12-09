<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\InvoiceTemplate;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\InvoiceTemplateController;

class InvoiceTemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar usuario autenticado y empresa
        $this->company = Company::factory()->create();
        $this->authUser = User::factory()->create(['company_id' => $this->company->id]);
        Auth::login($this->authUser);

        // Limpiar manualmente la colección de MongoDB antes de cada prueba
        InvoiceTemplate::truncate();
    }

    public function testCreateTemplateWithComplexData()
    {
        $controller = new InvoiceTemplateController();
        $request = new Request([
            'template_name' => 'Plantilla de Factura prueba',
            'company_id' => $this->company->id,
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
                    'field' => 'country',
                    'operator' => '==',
                    'value' => 'ES',
                    'conditions' => [
                        [
                            'field' => 'weight',
                            'operator' => '>',
                            'value' => 50,
                            'highlight' => '#09f',
                        ],
                        [
                            'field' => 'weight',
                            'operator' => '>=',
                            'value' => 80,
                            'row_highlight' => 'purple',
                        ],
                    ],
                ],
            ],
            'aggregations' => [
                [
                    'type' => 'sum',
                    'fields' => ['cost', 'real_weight'],
                ],
            ],
        ]);

        $response = $controller->create($request);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Plantilla creada', $responseData['message']);
        $this->assertArrayHasKey('template', $responseData);

        $template = $responseData['template'];

        $this->assertEquals('Plantilla de Factura prueba', $template['template_name']);
        $this->assertCount(13, $template['column_mappings']);
        $this->assertCount(1, $template['formulas']);
        $this->assertCount(2, $template['validation_rules']);
        $this->assertCount(1, $template['aggregations']);
    }

    public function testIndexTemplate()
    {
        $template = InvoiceTemplate::factory()->create([
            'company_id' => $this->company->id,
            'template_name' => 'Plantilla de Factura prueba',
        ]);

        $controller = new InvoiceTemplateController();
        $response = $controller->index();

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertCount(1, $responseData);
    }

    public function testIndexTemplateUnauthorized()
    {
        Auth::logout();

        $controller = new InvoiceTemplateController();
        $response = $controller->index();

        $this->assertEquals(401, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('No autorizado', $responseData['message']);
    }

    public function testShowTemplate()
    {
        $template = InvoiceTemplate::factory()->create([
            'company_id' => $this->company->id,
            'template_name' => 'Plantilla de Factura prueba',
        ]);

        $controller = new InvoiceTemplateController();
        $response = $controller->show($template->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Plantilla de Factura prueba', $responseData['template_name']);
    }

    public function testShowTemplateUnauthorized()
    {
        Auth::logout();

        $template = InvoiceTemplate::factory()->create([
            'company_id' => $this->company->id,
            'template_name' => 'Plantilla de Factura prueba',
        ]);

        $controller = new InvoiceTemplateController();
        $response = $controller->show($template->id);

        $this->assertEquals(401, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('No autorizado', $responseData['message']);
    }

    public function testCreateTemplateValidationFails()
    {
        $controller = new InvoiceTemplateController();
        $request = new Request([
            // Faltan campos requeridos
            'template_name' => '',
        ]);

        $response = $controller->create($request);

        $this->assertEquals(400, $response->status());
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('template_name', $responseData['errors']);
        $this->assertArrayHasKey('column_mappings', $responseData['errors']);
    }

    public function testUpdateTemplateWithComplexData()
    {
        $template = InvoiceTemplate::factory()->create([
            'company_id' => $this->company->id,
            'template_name' => 'Plantilla Anterior',
        ]);

        $controller = new InvoiceTemplateController();
        $request = new Request([
            'template_name' => 'Plantilla Actualizada',
            'column_mappings' => [
                'peso' => 'weight',
                'altura' => 'height',
                'ancho' => 'width',
                'largo' => 'lenght',
                'coste' => 'cost',
            ],
            'formulas' => [
                [
                    'new_column' => 'peso_total',
                    'formula' => '(lenght + width + height) / 3',
                ],
            ],
        ]);

        $response = $controller->update($request, $template->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Plantilla actualizada', $responseData['message']);
        $this->assertEquals('Plantilla Actualizada', $responseData['template']['template_name']);
        $this->assertCount(5, $responseData['template']['column_mappings']);
        $this->assertCount(1, $responseData['template']['formulas']);
    }

}
