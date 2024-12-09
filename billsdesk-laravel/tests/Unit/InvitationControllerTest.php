<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Invitation;
use App\Http\Controllers\InvitationController;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InvitationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetInvitationsReturnsInvitationsForValidToken()
    {
        $invitation = Invitation::factory()->create([
            'token' => 'valid_token',
        ]);

        $controller = new InvitationController();

        $response = $controller->getInvitations('valid_token');

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);


        $this->assertCount(1, $responseData);
        $this->assertEquals($invitation->toArray(), $responseData[0]);
    }

    public function testGetInvitationsReturnsEmptyArrayForInvalidToken()
    {
        $controller = new InvitationController();

        $response = $controller->getInvitations('invalid_token');

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertCount(0, $responseData);
    }

    public function testIndexReturnsAllInvitations()
    {
        $invitations = Invitation::factory()->count(3)->create();

        $controller = new InvitationController();

        $response = $controller->index();

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertCount(3, $responseData);
        $this->assertEquals($invitations->toArray(), $responseData);
    }

    public function testSendInvitationReturnsErrorForInvalidData()
    {
        $controller = new InvitationController();

        $response = $controller->sendInvitation(new Request());

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testSendInvitationReturnsErrorForExistingEmail()
    {
        // Crear los datos necesarios en la base de datos
        $role = Role::factory()->create();
        $company = Company::factory()->create();

        // Crear una invitación existente
        $existingInvitation = Invitation::factory()->create([
            'email' => 'test@test.com',
            'role_id' => $role->id,
            'company_id' => $company->id,
        ]);

        // Simular al usuario autenticado con la compañía asignada
        $user = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($user);

        // Crear el controlador
        $controller = new InvitationController();

        // Crear la solicitud con el mismo email
        $request = new Request([
            'email' => 'test@test.com',
            'role_id' => $role->id,
        ]);

        // Llamar al método sendInvitation
        $response = $controller->sendInvitation($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());
        $responseData = $response->getData(true);


        // Validar que el error corresponde al email duplicado
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['errors']);
    }

    public function testSendInvitationReturnsErrorForUnauthenticatedUser()
    {
        // Mockear Auth para simular un usuario no autenticado
        Auth::shouldReceive('user')->once()->andReturn(null);

        // Crear el controlador
        $controller = new InvitationController();

        // Crear la solicitud simulada
        $role = Role::factory()->create();
        $request = new Request([
            'email' => 'test@test.com',
            'role_id' => $role->id,
        ]);

        // Llamar al método sendInvitation
        $response = $controller->sendInvitation($request);

        // Validar la respuesta
        $this->assertEquals(401, $response->status());
        $responseData = $response->getData(true);

        // Validar que la respuesta contiene el mensaje de error esperado
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('User not authenticated', $responseData['error']);
    }

    public function testSendInvitationReturnsSuccessForValidData()
    {
        $role = Role::factory()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($user);

        $controller = new InvitationController();

        $request = new Request([
            'email' => 'test@test.com',
            'role_id' => $role->id,
        ]);

        $response = $controller->sendInvitation($request);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Invitation sent successfully.', $responseData['message']);
    }

    public function testResendInvitationReturnsErrorForInvalidData()
    {
        $controller = new InvitationController();

        $response = $controller->resendInvitation(new Request());

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
    }


    public function testResendInvitationReturnsErrorForEmailInvalid()
    {
        $controller = new InvitationController();

        $user = User::factory()->create();
        $this->actingAs($user);

        $request = new Request([
            'email' => 'tesest.com',
        ]);

        $response = $controller->resendInvitation($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['errors']);

        $this->assertEquals('The email field must be a valid email address.', $responseData['errors']['email'][0]);
    }

    public function testResendInvitationReturnsSuccessForValidData()
    {
        $controller = new InvitationController();

        $user = User::factory()->create();
        $this->actingAs($user);

        $request = new Request([
            'email' => 'test@test.com',
        ]);

        $invitation = Invitation::factory()->create([
            'email' => 'test@test.com',
        ]);


        $response = $controller->resendInvitation($request);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('message', $responseData);

        $this->assertEquals('Invitation sent successfully.', $responseData['message']);
    }


    public function testCancelInvitationReturnsErrorForInvalidData()
    {
        $controller = new InvitationController();

        $response = $controller->cancelInvitation(new Request());

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testCancelInvitationReturnsErrorForEmailInvalid()
    {
        $controller = new InvitationController();

        $user = User::factory()->create();
        $this->actingAs($user);

        $request = new Request([
            'email' => 'tesest.com',
        ]);

        $response = $controller->cancelInvitation($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['errors']);

        $this->assertEquals('The email field must be a valid email address.', $responseData['errors']['email'][0]);
    }

    public function testCancelInvitationReturnsErrorForInvitationNotFound()
    {
        $controller = new InvitationController();

        $user = User::factory()->create();
        $this->actingAs($user);

        $request = new Request([
            'email' => 'test@test.com',
        ]);

        $response = $controller->cancelInvitation($request);

        $this->assertEquals(404, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invitation not found', $responseData['error']);
    }

    public function testCancelInvitationReturnsSuccessForValidData()
    {
        $controller = new InvitationController();

        $user = User::factory()->create();
        $this->actingAs($user);

        $request = new Request([
            'email' => 'test@test.com',
        ]);

        $invitation = Invitation::factory()->create([
            'email' => 'test@test.com',
        ]);

        $response = $controller->cancelInvitation($request);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('message', $responseData);

        $this->assertEquals('Invitation canceled successfully.', $responseData['message']);
    }




}
