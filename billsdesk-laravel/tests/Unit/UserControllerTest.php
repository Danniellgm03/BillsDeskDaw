<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Role;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsPaginatedUsers()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        User::factory(9)->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $controller = new UserController();

        $request = new Request([
            'per_page' => 5,
        ]);

        $response = $controller->index($request);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals(5, count($responseData['data']));
        $this->assertEquals(2, $responseData['last_page']);
        $this->assertEquals(5, $responseData['per_page']);
        $this->assertEquals(10, $responseData['total']);
        $this->assertCount(5, $responseData['data']);

        $this->assertEquals($authUser->id, $responseData['data'][0]['id']);

    }


    public function testIndexWithoutSearch()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        User::factory(9)->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $controller = new UserController();

        $request = new Request([
            'per_page' => 5,
        ]);

        $response = $controller->index($request);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals(5, count($responseData['data']));
        $this->assertEquals(2, $responseData['last_page']);
        $this->assertEquals(5, $responseData['per_page']);
        $this->assertEquals(10, $responseData['total']);
        $this->assertCount(5, $responseData['data']);

        $this->assertEquals($authUser->id, $responseData['data'][0]['id']);
    }

    public function testIndexWithSearch()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        User::factory(9)->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $controller = new UserController();

        $request = new Request([
            'per_page' => 5,
            'search' => $authUser->name,
        ]);

        $response = $controller->index($request);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals(1, count($responseData['data']));
        $this->assertEquals(1, $responseData['last_page']);
        $this->assertEquals(5, $responseData['per_page']);
        $this->assertEquals(1, $responseData['total']);
        $this->assertCount(1, $responseData['data']);

        $this->assertEquals($authUser->id, $responseData['data'][0]['id']);
    }

    public function testIndexWithUnthenticatedUser()
    {
        $controller = new UserController();

        $request = new Request([
            'per_page' => 5,
        ]);

        $response = $controller->index($request);

        $this->assertEquals(401, $response->status());
    }

    public function testStoreUser()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'password' => 'password',
            'role_id' => $role->id,
        ]);

        $response = $controller->store($request);

        $this->assertEquals(201, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('John Doe', $responseData['name']);
        $this->assertEquals('test@test.com', $responseData['email']);
        $this->assertEquals($role->id, $responseData['role_id']);
    }


    public function testStoreUserWithoutName()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $request = new Request([
            'email' => 'test@test.com',
            'password' => 'password',
            'role_id' => $role->id,
        ]);

        $response = $controller->store($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);

        $this->assertArrayHasKey('name', $responseData['errors']);
    }

    public function testStoreUserWithoutEmail()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'password' => 'password',
            'role_id' => $role->id,
        ]);

        $response = $controller->store($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);

        $this->assertArrayHasKey('email', $responseData['errors']);
    }

    public function testStoreUserWithoutPassword()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'role_id' => $role->id,
        ]);

        $response = $controller->store($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);

        $this->assertArrayHasKey('password', $responseData['errors']);
    }

    public function testStoreUserWithoutRoleId()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        $response = $controller->store($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);

        $this->assertArrayHasKey('role_id', $responseData['errors']);
    }

    public function testStoreUserWithExistingEmail()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => $user->email,
            'password' => 'password',
            'role_id' => $role->id,
        ]);

        $response = $controller->store($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);

        $this->assertArrayHasKey('email', $responseData['errors']);
    }

    public function testStoreUserWithoutAuthenticatedUser()
    {
        $controller = new UserController();

        Role::factory()->create();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'password' => 'password',
            'role_id' => 1,
        ]);

        $response = $controller->store($request);

        $this->assertEquals(401, $response->status());
    }


    public function testStoreUserWithInvalidRoleId()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'password' => 'password',
            'role_id' => 2,
        ]);

        $response = $controller->store($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);

        $this->assertArrayHasKey('role_id', $responseData['errors']);
    }

    public function testShow(){
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $controller = new UserController();

        $response = $controller->show($user->id);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals($user->id, $responseData['id']);
        $this->assertEquals($user->name, $responseData['name']);
        $this->assertEquals($user->email, $responseData['email']);
        $this->assertEquals($user->role_id, $responseData['role_id']);
    }

    public function testShowWithUnauthenticatedUser(){
        $controller = new UserController();

        $user = User::factory()->create();

        $response = $controller->show($user->id);

        $this->assertEquals(401, $response->status());
    }

    public function testShowWithInvalidUser(){
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $response = $controller->show(100);

        $this->assertEquals(404, $response->status());
    }

    public function testShowWithDifferentCompany(){
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company2->id,
            'role_id' => $role->id,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $response = $controller->show($user->id);

        $this->assertEquals(403, $response->status());
    }

    public function testUpdateUser(){
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'role_id' => $role->id,
        ]);

        $response = $controller->update($request, $user->id);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals($user->id, $responseData['id']);
        $this->assertEquals('John Doe', $responseData['name']);
        $this->assertEquals('test@test.com', $responseData['email']);
        $this->assertEquals($role->id, $responseData['role_id']);
    }


    public function testUpdateUserWithInvalidRoleId(){
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $role2 = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role2->id,
        ]);

        Auth::login($authUser);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'role_id' => 3,
        ]);

        $response = $controller->update($request, $user->id);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);

        $this->assertArrayHasKey('role_id', $responseData['errors']);
    }

    public function testUpdateUserWithDifferentCompany(){
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company2->id,
            'role_id' => $role->id,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'role_id' => $role->id,
        ]);

        $response = $controller->update($request, $user->id);

        $this->assertEquals(403, $response->status());
    }

    public function testUpdateUserWithUnauthenticatedUser(){
        $controller = new UserController();

        $user = User::factory()->create();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'role_id' => 1,
        ]);

        $response = $controller->update($request, $user->id);

        $this->assertEquals(401, $response->status());

    }


    public function testUpdateUserWithInvalidUser(){
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
            'role_id' => $role->id,
        ]);

        $response = $controller->update($request, 100);

        $this->assertEquals(404, $response->status());
    }

    public function testDestroy()
    {
        $company = Company::factory()->create();

        $role = Role::factory()->create([
            'name' => 'User',
        ]);

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $userToDelete = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $response = $controller->destroy($userToDelete->id);

        $this->assertEquals(204, $response->status());

        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }


    public function testDestroyWithInvalidUser(){
        $company = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $response = $controller->destroy(100);

        $this->assertEquals(404, $response->status());
    }

    public function testDestroyWithDifferentCompany(){
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();

        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company2->id,
            'role_id' => $role->id,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        Auth::login($authUser);

        $controller = new UserController();

        $response = $controller->destroy($user->id);

        $this->assertEquals(403, $response->status());
    }

    public function testMeProfile()
    {
        // Crear una compañía y un rol
        $company = Company::factory()->create();
        $role = Role::factory()->create();

        // Crear un usuario autenticado
        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        // Simular autenticación
        $this->actingAs($authUser);

        // Crear el controlador
        $controller = new UserController();

        // Crear una instancia de Request simulada
        $request = new Request();

        // Llamar al método meProfile
        $response = $controller->meProfile($request);

        // Validar la respuesta
        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        // Validar los datos del usuario
        $this->assertEquals($authUser->id, $responseData['id']);
        $this->assertEquals($authUser->name, $responseData['name']);
        $this->assertEquals($authUser->email, $responseData['email']);
        $this->assertEquals($authUser->role_id, $responseData['role_id']);
    }

    public function testMeProfileWithoutAuthenticatedUser()
    {
        $controller = new UserController();

        $request = new Request();

        $response = $controller->meProfile($request);


        $this->assertEquals(401, $response->status());
    }



    public function testUpdateProfile()
    {
        $company = Company::factory()->create();
        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $this->actingAs($authUser);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
        ]);

        $response = $controller->updateProfile($request);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals($authUser->id, $responseData['id']);
        $this->assertEquals('John Doe', $responseData['name']);
        $this->assertEquals('test@test.com', $responseData['email']);
        $this->assertEquals($authUser->role_id, $responseData['role_id']);
    }

    public function testUpdateProfileWithoutAuthenticatedUser()
    {
        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test@test.com',
        ]);

        $response = $controller->updateProfile($request);

        $this->assertEquals(401, $response->status());
    }


    public function testUpdateProfileWithInvalidDataEmail()
    {
        $company = Company::factory()->create();
        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $this->actingAs($authUser);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => 'test',
        ]);

        $response = $controller->updateProfile($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);

        $this->assertArrayHasKey('email', $responseData['errors']);
    }


    public function testUpdateProfileWithExistingEmail()
    {
        $company = Company::factory()->create();
        $role = Role::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);

        $this->actingAs($authUser);

        $controller = new UserController();

        $request = new Request([
            'name' => 'John Doe',
            'email' => $user->email,
        ]);

        $response = $controller->updateProfile($request);

        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);

        $this->assertArrayHasKey('email', $responseData['errors']);
    }


}
