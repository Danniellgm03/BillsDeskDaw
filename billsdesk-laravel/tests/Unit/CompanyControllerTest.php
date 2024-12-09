<?php

namespace Tests\Unit;


use Tests\TestCase;
use App\Models\Company;
use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsAllCompanies()
    {
        // Crear compañías en la base de datos
        Company::factory()->count(3)->create();

        // Crear el controlador
        $controller = new CompanyController();

        // Llamar al método index
        $response = $controller->index();

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $this->assertCount(3, $response->getData());

        $responseData = $response->getData();


        foreach ($responseData as $company) {
            $this->assertNotNull(Company::find($company->id));
        }

    }

    public function testIndexReturnEmptyArray()
    {
        // Crear el controlador
        $controller = new CompanyController();

        // Llamar al método index
        $response = $controller->index();

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $this->assertCount(0, $response->getData());
    }

    public function testStoreCompany()
    {
        $controller = new CompanyController();

        $company = Company::factory()->make();

        $request = new Request([
            'name' => $company->name,
            'address' => $company->address
        ]);

        $response = $controller->store($request);

        $this->assertEquals(201, $response->status());

        $this->assertNotNull(Company::find($response->getData()->id));
    }

    public function testStoreCompanyWithoutName()
    {
        $controller = new CompanyController();

        $company = Company::factory()->make();

        $request = new Request([
            'address' => $company->address
        ]);

        $response = $controller->store($request);

        $this->assertEquals(422, $response->status());
    }

    public function testStoreCompanyWithoutAddress()
    {
        $controller = new CompanyController();

        $company = Company::factory()->make();

        $request = new Request([
            'name' => $company->name
        ]);

        $response = $controller->store($request);

        $this->assertEquals(422, $response->status());
    }

    public function testStoreInvalidMax255Name()
    {
        $controller = new CompanyController();

        $company = Company::factory()->make();

        $request = new Request([
            'name' => str_repeat('a', 256),
            'address' => $company->address
        ]);

        $response = $controller->store($request);

        $this->assertEquals(422, $response->status());
    }

    public function testShowCompany()
    {
        $company = Company::factory()->create();

        $controller = new CompanyController();

        $response = $controller->show($company->id);

        $this->assertEquals(200, $response->status());
        $this->assertEquals($company->id, $response->getData()->id);
    }

    public function testShowCompanyNotFound()
    {
        $controller = new CompanyController();

        $response = $controller->show(1);

        $this->assertEquals(404, $response->status());
    }

    public function testUpdateCompany()
    {
        $company = Company::factory()->create();

        $controller = new CompanyController();

        $company->name = 'New Name';
        $company->address = 'New Address';

        $request = new Request([
            'name' => $company->name,
            'address' => $company->address
        ]);

        $response = $controller->update($request, $company->id);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('New Name', $response->getData()->name);
        $this->assertEquals('New Address', $response->getData()->address);
    }


    public function testUpdateCompanyNotFound()
    {
        $controller = new CompanyController();

        $company = Company::factory()->make();

        $request = new Request([
            'name' => $company->name,
            'address' => $company->address
        ]);

        $response = $controller->update($request, 1);

        $this->assertEquals(404, $response->status());
    }

    public function testUpdateCompanyWithoutName()
    {
        $company = Company::factory()->create();

        $controller = new CompanyController();

        $request = new Request([
            'address' => $company->address
        ]);

        $response = $controller->update($request, $company->id);

        $this->assertEquals(422, $response->status());
    }

    public function testUpdateCompanyWithoutAddress()
    {
        $company = Company::factory()->create();

        $controller = new CompanyController();

        $request = new Request([
            'name' => $company->name
        ]);

        $response = $controller->update($request, $company->id);

        $this->assertEquals(422, $response->status());
    }

    public function testUpdateInvalidMax255Name()
    {
        $company = Company::factory()->create();

        $controller = new CompanyController();

        $request = new Request([
            'name' => str_repeat('a', 256),
            'address' => $company->address
        ]);

        $response = $controller->update($request, $company->id);

        $this->assertEquals(422, $response->status());
    }

    public function testDestroyCompany()
    {
        $company = Company::factory()->create();

        $controller = new CompanyController();

        $response = $controller->destroy($company->id);

        $this->assertEquals(200, $response->status());
        $this->assertNull(Company::find($company->id));
    }

    public function testDestroyCompanyNotFound()
    {
        $controller = new CompanyController();

        $response = $controller->destroy(1);

        $this->assertEquals(404, $response->status());
    }

    public function testMeCompany()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new CompanyController();

        $response = $controller->meCompany();

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);
        $this->assertEquals($company->id, $responseData['id']);
        $this->assertEquals($company->name, $responseData['name']);
        $this->assertEquals($company->address, $responseData['address']);
    }


    public function testMeCompanyWithoutAuthUser()
    {
        $controller = new CompanyController();

        $response = $controller->meCompany();

        $this->assertEquals(404, $response->status());
    }

    public function testMeCompanyWithoutCompany()
    {
        $user = User::factory()->create();

        $user->company_id = null;

        $this->actingAs($user);

        $controller = new CompanyController();

        $response = $controller->meCompany();

        $this->assertEquals(404, $response->status());
    }


}
