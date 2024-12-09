<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\InvoiceController;
use App\Models\File;
use App\Models\InvoiceTemplate;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InvoiceImport;
use Mockery;


class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar empresa y usuario autenticado
        $this->company = Company::factory()->create();
        $this->authUser = User::factory()->create(['company_id' => $this->company->id]);

        Invoice::truncate();
    }

    public function testIndexReturnsPaginatedInvoices()
    {
        Auth::login($this->authUser);

        Invoice::factory(10)->create([
            'company_id' => $this->company->id,
        ]);

        $controller = new InvoiceController();

        $request = new Request([
            'per_page' => 5,
        ]);

        $response = $controller->index($request);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('data', $responseData['data']); // Los datos paginados
        $this->assertCount(5, $responseData['data']['data']); // Deben devolverse 5 elementos
        $this->assertEquals(10, $responseData['data']['total']); // Total de elementos en la base de datos
    }

    public function testIndexReturnsPaginatedInvoicesWithSearch()
    {
        Auth::login($this->authUser);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'name_invoice' => 'Factura 1',
        ]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'name_invoice' => 'Factura 2',
        ]);

        $controller = new InvoiceController();

        $request = new Request([
            'per_page' => 5,
            'search' => 'Factura 1',
        ]);

        $response = $controller->index($request);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('data', $responseData['data']); // Los datos paginados
        $this->assertCount(1, $responseData['data']['data']); // Debe devolverse 1 elemento
        $this->assertEquals(1, $responseData['data']['total']); // Total de elementos en la base de datos
    }

    public function testIndexWithUnAunthorizedUser()
    {
        Auth::logout();

        $controller = new InvoiceController();

        $response = $controller->index(new Request());

        $this->assertEquals(401, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals('No autorizado', $responseData['message']);
    }

    public function testShowReturnsInvoice()
    {
        Auth::login($this->authUser);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $controller = new InvoiceController();

        $response = $controller->show($invoice->id);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals($invoice->id, $responseData['id']);
    }

    public function testShowWithUnAunthorizedUser()
    {
        Auth::logout();

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $controller = new InvoiceController();

        $response = $controller->show($invoice->id);

        $this->assertEquals(401, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals('No autorizado', $responseData['message']);
    }


    public function testCreateInvoice()
    {
        Auth::login($this->authUser);

        $controller = new InvoiceController();

        $file = File::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $request = new Request([
            'file_id' => $file->id,
            'template_id' => 'template_id',
            'name_invoice' => 'Factura de prueba',
        ]);

        $response = $controller->create($request);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals('Factura creada', $responseData['message']);
        $this->assertEquals('Factura de prueba', $responseData['invoice']['name_invoice']);
    }

    public function testCreateInvoiceWithValidationFails()
    {
        Auth::login($this->authUser);

        $controller = new InvoiceController();

        $request = new Request([
            'file_id' => 1,
            'template_id' => 'template_id',
            'name_invoice' => 123,
        ]);

        $response = $controller->create($request);

        $this->assertEquals(400, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('name_invoice', $responseData['errors']);
    }

    public function testCreateInvoiceWithUnAunthorizedUser()
    {
        Auth::logout();

        $controller = new InvoiceController();

        $file = File::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $request = new Request([
            'file_id' => $file->id,
            'template_id' => 'template_id',
            'name_invoice' => 'Factura de prueba',
        ]);

        $response = $controller->create($request);

        $this->assertEquals(401, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals('No autorizado', $responseData['message']);
    }

    public function testGetTemplate()
    {
        Auth::login($this->authUser);

        $template = InvoiceTemplate::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'template_id' => $template->_id,
        ]);

        $controller = new InvoiceController();

        $response = $controller->getTemplate($invoice->id);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('template', $responseData);
        $this->assertEquals($template->id, $responseData['template']['id']);
        $this->assertEquals($template->template_name, $responseData['template']['template_name']);
        $this->assertIsArray($responseData['template']['column_mappings']);
    }

    public function testGetTemplateWithUnAunthorizedUser()
    {
        Auth::logout();

        $template = InvoiceTemplate::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'template_id' => $template->_id,
        ]);

        $controller = new InvoiceController();

        $response = $controller->getTemplate($invoice->id);

        $this->assertEquals(401, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals('No autorizado', $responseData['message']);
    }

    public function testGetCorrectionRules()
    {
        Auth::login($this->authUser);

        $template = InvoiceTemplate::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'template_id' => $template->_id,
        ]);

        $controller = new InvoiceController();

        $response = $controller->getCorrectionRules($template->_id);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('correction_rules', $responseData);
        $this->assertIsArray($responseData['correction_rules']);
    }



    //update
    public function testUpdateInvoice()
    {
        Auth::login($this->authUser);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $controller = new InvoiceController();

        $request = new Request([
            'name_invoice' => 'Factura de prueba actualizada',
        ]);

        $response = $controller->update($request, $invoice->id);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals('Factura actualizada', $responseData['message']);
        $this->assertEquals('Factura de prueba actualizada', $responseData['invoice']['name_invoice']);
    }

    public function testUpdateInvoiceWithValidationFails()
    {
        Auth::login($this->authUser);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $controller = new InvoiceController();

        $request = new Request([
            'name_invoice' => 123,
        ]);

        $response = $controller->update($request, $invoice->id);

        $this->assertEquals(400, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('name_invoice', $responseData['errors']);
    }


    public function testUpdateInvoiceWithUnAunthorizedUser()
    {
        Auth::logout();

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $controller = new InvoiceController();

        $request = new Request([
            'name_invoice' => 'Factura de prueba actualizada',
        ]);

        $response = $controller->update($request, $invoice->id);

        $this->assertEquals(401, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals('No autorizado', $responseData['message']);
    }


}
