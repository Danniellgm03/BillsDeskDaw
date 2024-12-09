<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;

class ContactControllerTest extends TestCase
{

    use RefreshDatabase;

    public function testIndexReturnsAllContactsForAuthenticatedUser()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);

        $contacts = Contact::factory(3)->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new ContactController();

        $response = $controller->index();

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData();

        $this->assertCount(3, $responseData);

        foreach ($responseData as $contact) {
            $this->assertNotNull(Contact::find($contact->id));
        }
    }

    public function testIndexReturnsErrorIfCompanyNotFound()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $user->company_id = null;

        $controller = new ContactController();

        $response = $controller->index();

        $this->assertEquals(404, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Empresa no encontrada para el usuario autenticado', $responseData->error);
    }

    public function testIndexWithoutAuthenticatedUser()
    {
        $controller = new ContactController();

        $response = $controller->index();

        $this->assertEquals(401, $response->status());
    }

    public function testStoreCreatesNewContactForAuthenticatedUser()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new ContactController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        $response = $controller->store($request);

        $this->assertEquals(201, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('John Doe', $responseData->name);
        $this->assertEquals('test@test.com', $responseData->email);
        $this->assertEquals('1234567890', $responseData->phone);
        $this->assertEquals('123 Main St', $responseData->address);
    }

    public function testStoreReturnsErrorIfCompanyNotFound()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $user->company_id = null;

        $controller = new ContactController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        $response = $controller->store($request);

        $this->assertEquals(404, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Empresa no encontrada para el usuario autenticado', $responseData->error);
    }

    public function testStoreReturnsErrorIfValidationFailsName()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new ContactController();

        $request = new Request([
            'name' => '',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        $response = $controller->store($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('The name field is required.', $responseData->errors->name[0]);
    }

    public function testStoreWithoutAuthenticatedUser()
    {
        $controller = new ContactController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        $response = $controller->store($request);

        $this->assertEquals(401, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Usuario no autenticado', $responseData->error);
    }

    public function testShowReturnsSpecificContactForAuthenticatedUser()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);

        $contact = Contact::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new ContactController();

        $response = $controller->show($contact->id);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData();

        $this->assertEquals($contact->id, $responseData->id);
    }

    public function testShowReturnsErrorIfCompanyNotFound()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $user->company_id = null;

        $controller = new ContactController();

        $response = $controller->show(1);

        $this->assertEquals(404, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Empresa no encontrada para el usuario autenticado', $responseData->error);
    }

    public function testShowWithoutAuthenticatedUser()
    {
        $controller = new ContactController();

        $response = $controller->show(1);

        $this->assertEquals(401, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Usuario no autenticado', $responseData->error);
    }

    public function testShowReturnsErrorIfContactNotFound()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new ContactController();

        $response = $controller->show(1);

        $this->assertEquals(404, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Contacto no encontrado', $responseData->error);
    }

    public function testUpdateUpdatesContactForAuthenticatedUser()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);

        $contact = Contact::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new ContactController();

        $request = new Request([
            'name' => 'Jane Doe',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        $response = $controller->update($request, $contact->id);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Jane Doe', $responseData->name);
        $this->assertEquals('test@test.com', $responseData->email);
        $this->assertEquals('1234567890', $responseData->phone);
    }

    public function testUpdateReturnsErrorIfCompanyNotFound()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $user->company_id = null;

        $controller = new ContactController();

        $request = new Request([
            'name' => 'Jane Doe',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        $response = $controller->update($request, 1);

        $this->assertEquals(404, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Empresa no encontrada para el usuario autenticado', $responseData->error);
    }

    public function testUpdateReturnsErrorIfContactNotFound()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new ContactController();

        $request = new Request([
            'name' => 'Jane Doe',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        $response = $controller->update($request, 1);

        $this->assertEquals(404, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Contacto no encontrado', $responseData->error);
    }

    public function testUpdateReturnsErrorIfValidationFailsName()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);

        $contact = Contact::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new ContactController();

        $request = new Request([
            'name' => '',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        $response = $controller->update($request, $contact->id);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('The name field is required.', $responseData->errors->name[0]);
    }

    public function testUpdateWithoutAuthenticatedUser()
    {
        $controller = new ContactController();

        $request = new Request([
            'name' => 'Jane Doe',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        $response = $controller->update($request, 1);

        $this->assertEquals(401, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Usuario no autenticado', $responseData->error);
    }

    public function testDestroyDeletesContactForAuthenticatedUser()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);

        $contact = Contact::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new ContactController();

        $response = $controller->destroy($contact->id);

        $this->assertEquals(204, $response->status());

        $this->assertNull(Contact::find($contact->id));
    }

    public function testDestroyReturnsErrorIfCompanyNotFound()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $user->company_id = null;

        $controller = new ContactController();

        $response = $controller->destroy(1);

        $this->assertEquals(404, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Empresa no encontrada para el usuario autenticado', $responseData->error);
    }

    public function testDestroyReturnsErrorIfContactNotFound()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $controller = new ContactController();

        $response = $controller->destroy(1);

        $this->assertEquals(404, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Contacto no encontrado', $responseData->error);
    }

    public function testDestroyWithoutAuthenticatedUser()
    {
        $controller = new ContactController();

        $response = $controller->destroy(1);

        $this->assertEquals(401, $response->status());

        $responseData = $response->getData();

        $this->assertEquals('Usuario no autenticado', $responseData->error);
    }
}
