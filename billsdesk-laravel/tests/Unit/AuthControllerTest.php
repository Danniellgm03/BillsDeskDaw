<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Invitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Notifications\ForgotPasswordNotification;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginWithValidCredentials()
    {
        // Mock del role con permisos
        $roleMock = Mockery::mock();
        $roleMock->permissions = ['view_dashboard', 'edit_profile'];

        // Simulación de un usuario con atributos definidos como propiedades de PHP
        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('createToken')->andReturn((object)['plainTextToken' => 'token']);
        $userMock->shouldReceive('getAttribute')->with('role')->andReturn($roleMock);

        // Mock del método jsonSerialize para evitar el error
        $userMock->shouldReceive('jsonSerialize')->andReturn([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@test.com',
            'role' => $roleMock,
        ]);

        // Mock de Auth::attempt
        Auth::shouldReceive('attempt')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($userMock);

        // Creación de una instancia de AuthController
        $authController = new AuthController();

        // Creación de una instancia de Request con los datos de la petición
        $request = new Request([
            'email' => 'test@test.com',
            'password' => 'password'
        ]);

        // Llamada al método login del controlador
        $response = $authController->login($request);

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Bearer', $responseData['token_type']);
        $this->assertEquals('token', $responseData['access_token']);
        $this->assertEquals('Test User', $responseData['user']['name']);
        $this->assertEquals('test@test.com', $responseData['user']['email']);
        $this->assertArrayHasKey('access_token', $responseData);
        $this->assertEquals(['view_dashboard', 'edit_profile'], $responseData['permissions']);
    }

    public function testLoginWithInvalidCredentials()
    {
        // Mock de Auth::attempt
        Auth::shouldReceive('attempt')->andReturn(false);

        // Creación de una instancia de AuthController
        $authController = new AuthController();

        // Creación de una instancia de Request con los datos de la petición
        $request = new Request([
            'email' => 'test@test.com',
            'password' => 'password'
        ]);

        // Llamada al método login del controlador
        $response = $authController->login($request);

        // Validar la respuesta
        $this->assertEquals(401, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Credenciales incorrectas', $responseData['error']);
    }


    public function testRegisterWithValidData()
    {
        // Crear un rol con permisos específicos
        Role::factory()->create([
            'id' => 1,
            'permissions' => ['view_dashboard', 'edit_profile'],
        ]);

        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Llamar al método register
        $response = $authController->register($request);

        // Validar la respuesta
        $this->assertEquals(201, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Bearer', $responseData['token_type']);
        $this->assertMatchesRegularExpression('/^\d+\|[a-zA-Z0-9]+$/', $responseData['access_token']);
        $this->assertEquals('Test User', $responseData['user']['name']);
        $this->assertEquals('test@test.com', $responseData['user']['email']);
        $this->assertArrayHasKey('access_token', $responseData);
        $this->assertEquals(['view_dashboard', 'edit_profile'], $responseData['permissions']);
    }

    public function testRegisterWithInvalidDataPasswordConfirmedFail()
    {
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password123',
        ]);

        // Llamar al método register
        $response = $authController->register($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('password', $responseData['errors']);

        $this->assertEquals('The password field confirmation does not match.', $responseData['errors']['password'][0]);

    }

    public function testRegisterWithInvalidMail(){
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'Test User',
            'email' => 'test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Llamar al método register
        $response = $authController->register($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['errors']);

        $this->assertEquals('The email field must be a valid email address.', $responseData['errors']['email'][0]);
    }

    public function testRegisterWithEmptyMail(){
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'Test User',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Llamar al método register
        $response = $authController->register($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['errors']);

        $this->assertEquals('The email field is required.', $responseData['errors']['email'][0]);
    }

    public function testRegisterWithEmptyName(){
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => '',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Llamar al método register
        $response = $authController->register($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('name', $responseData['errors']);

        $this->assertEquals('The name field is required.', $responseData['errors']['name'][0]);
    }

    public function testRegisterWithEmptyPassword(){
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        // Llamar al método register
        $response = $authController->register($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('password', $responseData['errors']);

        $this->assertEquals('The password field is required.', $responseData['errors']['password'][0]);
    }

    public function testRegisterUniqueMailValidatorNotAllow()
    {
        // Crear un rol con id = 1 para cumplir con la clave foránea
        Role::factory()->create([
            'id' => 1,
            'permissions' => ['view_dashboard', 'edit_profile'],
        ]);

        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud para el primer registro
        $request = new Request([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Llamar al método register por primera vez
        $response = $authController->register($request);

        // Validar que el primer registro fue exitoso
        $this->assertEquals(201, $response->status());

        // Crear una nueva solicitud con el mismo email
        $request = new Request([
            'name' => 'Test User 2',
            'email' => 'test@test.com', // Misma dirección de correo
            'password' => 'password2',
            'password_confirmation' => 'password2',
        ]);

        // Llamar al método register por segunda vez
        $response = $authController->register($request);

        // Validar que la segunda solicitud devuelve un error de validación
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        // Validar que el error está relacionado con el email duplicado
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['errors']);
        $this->assertEquals('The email has already been taken.', $responseData['errors']['email'][0]);
    }

    public function testMin8CharactersPasswordValidatorNotAllow()
    {
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        // Llamar al método register
        $response = $authController->register($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('password', $responseData['errors']);

        $this->assertEquals('The password field must be at least 8 characters.', $responseData['errors']['password'][0]);
    }

    public function testLogout()
    {
        // Simular un usuario autenticado
        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('currentAccessToken->delete')->once();

        // Simular la solicitud con el usuario autenticado
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('user')->once()->andReturn($userMock);

        // Crear una instancia del controlador
        $authController = new AuthController();

        // Llamar al método logout
        $response = $authController->logout($requestMock);

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        // Validar el mensaje devuelto
        $this->assertEquals('Sesión cerrada exitosamente.', $responseData['message']);
    }



    public function testRegisterWithInvitationValidData()
    {
        // Crear un rol con permisos específicos
        $role = Role::factory()->create([
            'id' => 1,
            'permissions' => ['view_dashboard', 'edit_profile'],
        ]);

        // Crear una compañía ficticia
        $company = \App\Models\Company::factory()->create([
            'id' => 1,
        ]);

        // Crear una invitación válida
        $invitation = \App\Models\Invitation::factory()->create([
            'email' => 'invitee@test.com',
            'token' => 'valid_token',
            'accepted' => false,
            'role_id' => $role->id,
            'company_id' => $company->id,
        ]);

        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'New User',
            'email' => 'invitee@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'valid_token',
        ]);

        // Llamar al método registerWithInvitation
        $response = $authController->registerWithInvitation($request);

        // Validar la respuesta
        $this->assertEquals(201, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Bearer', $responseData['token_type']);
        $this->assertArrayHasKey('access_token', $responseData);
        $this->assertEquals('New User', $responseData['user']['name']);
        $this->assertEquals('invitee@test.com', $responseData['user']['email']);
        $this->assertEquals(['view_dashboard', 'edit_profile'], $responseData['permissions']);

        // Validar que la invitación fue marcada como aceptada
        $this->assertEquals('1', $invitation->fresh()->accepted);

        // Validar que el usuario fue creado en la base de datos
        $this->assertDatabaseHas('users', [
            'email' => 'invitee@test.com',
            'name' => 'New User',
        ]);
    }

    public function testRegisterWithInvitationInvalidToken()
    {
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'New User',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Llamar al método registerWithInvitation
        $response = $authController->registerWithInvitation($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals('The token field is required.', $responseData['errors']['token'][0]);

    }

    public function testRegisterWithInvitationInvalidEmail()
    {
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'New User',
            'email' => 'test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'valid_token',
        ]);

        // Llamar al método registerWithInvitation
        $response = $authController->registerWithInvitation($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals('The email field must be a valid email address.', $responseData['errors']['email'][0]);

    }

    public function testRegisterWithInvitationEmptyEmail()
    {
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'New User',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'valid_token',
        ]);

        // Llamar al método registerWithInvitation
        $response = $authController->registerWithInvitation($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals('The email field is required.', $responseData['errors']['email'][0]);

    }

    public function testRegisterWithInvitationEmptyName()
    {
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => '',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'valid_token',
        ]);

        // Llamar al método registerWithInvitation
        $response = $authController->registerWithInvitation($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('name', $responseData['errors']);
    }


    public function testRegisterWithInvitationEmptyPassword()
    {
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request([
            'name' => 'New User',
            'email' => 'test@test.com',
            'password' => '',
            'password_confirmation' => '',
            'token' => 'valid_token',
        ]);

        // Llamar al método registerWithInvitation
        $response = $authController->registerWithInvitation($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('password', $responseData['errors']);
    }

    public function testForgotPasswordWithValidEmail()
    {
        // Crear un usuario de prueba
        $user = User::factory()->create(['email' => 'test@test.com']);

        // Mockear la notificación
        Notification::fake();

        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request(['email' => 'test@test.com']);

        // Llamar al método forgotPassword
        $response = $authController->forgotPassword($request);

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);
        $this->assertEquals('Si el correo existe, se enviará un email con instrucciones', $responseData['message']);

        // Verificar que se envió la notificación
        Notification::assertSentTo($user, ForgotPasswordNotification::class);
    }

    public function testForgotPasswordWithNonexistentEmail()
    {
        // Mockear la notificación
        Notification::fake();

        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request(['email' => 'nonexistent@test.com']);

        // Llamar al método forgotPassword
        $response = $authController->forgotPassword($request);

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);
        $this->assertEquals('Si el correo existe, se enviará un email con instrucciones', $responseData['message']);

        // Verificar que no se envió ninguna notificación
        Notification::assertNothingSent();
    }

    public function testForgotPasswordWithEmptyEmail()
    {
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request(['email' => '']);

        // Llamar al método forgotPassword
        $response = $authController->forgotPassword($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals('The email field is required.', $responseData['errors']['email'][0]);
    }

    public function testForgotPasswordWithInvalidEmailFormat()
    {
        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request(['email' => 'invalid-email']);

        // Llamar al método forgotPassword
        $response = $authController->forgotPassword($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals('The email field must be a valid email address.', $responseData['errors']['email'][0]);
    }

    public function testForgotPasswordGeneratesTokenForValidEmail()
    {
        // Crear un usuario de prueba
        $user = User::factory()->create(['email' => 'test@test.com']);

        // Mockear el broker de Password
        Password::shouldReceive('broker')
            ->once()
            ->andReturn(Mockery::mock()->shouldReceive('createToken')
                ->once()
                ->with(Mockery::on(function ($arg) use ($user) {
                    return $arg->is($user); // Verifica que el argumento sea el usuario creado
                }))
                ->andReturn('mocked_token')
                ->getMock());

        // Crear el controlador
        $authController = new AuthController();

        // Crear la solicitud simulada
        $request = new Request(['email' => 'test@test.com']);

        // Llamar al método forgotPassword
        $response = $authController->forgotPassword($request);

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);
        $this->assertEquals('Si el correo existe, se enviará un email con instrucciones', $responseData['message']);
    }

    public function testResetPasswordEmailIsRequired()
    {
        $request = new Request([
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'token' => 'valid_token',
        ]);

        $authController = new AuthController();
        $response = $authController->resetPassword($request);

        $this->assertEquals(422, $response->status());
        $this->assertArrayHasKey('errors', $response->getData(true));
        $this->assertEquals('The email field is required.', $response->getData(true)['errors']['email'][0]);
    }

    public function testResetPasswordTokenIsRequired()
    {
        $request = new Request([
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'token' => '',
        ]);

        $authController = new AuthController();
        $response = $authController->resetPassword($request);

        $this->assertEquals(422, $response->status());
        $this->assertArrayHasKey('errors', $response->getData(true));
        $this->assertEquals('The token field is required.', $response->getData(true)['errors']['token'][0]);
    }

    public function testResetPasswordUserNotFound()
    {
        // Crear un mock para el método estático User::where utilizando el contenedor de Laravel
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('where')
                ->with('email', 'test@test.com')
                ->andReturnSelf();
            $mock->shouldReceive('first')->andReturn(null);
        });

        // Crear una solicitud simulada
        $request = new Request([
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'token' => 'valid_token',
        ]);

        // Crear una instancia del controlador
        $authController = new AuthController();

        // Llamar al método resetPassword
        $response = $authController->resetPassword($request);

        // Validar la respuesta
        $this->assertEquals(404, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Usuario no encontrado', $responseData['error']);
    }



    public function testResetPasswordSuccess()
    {
        // Crear un usuario de prueba
        $user = User::factory()->create(['email' => 'test@test.com']);

        // Mockear el Password broker directamente
        Password::shouldReceive('broker')
            ->andReturnSelf();

        Password::shouldReceive('tokenExists')
            ->once()
            ->with(Mockery::type(User::class), 'valid_token')
            ->andReturn(true);

        Password::shouldReceive('reset')
            ->once()
            ->with(
                [
                    'email' => 'test@test.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'token' => 'valid_token',
                ],
                Mockery::on(function ($callback) use ($user) {
                    // Ejecutar el callback para simular el reset
                    $callback($user, 'password123');
                    return true;
                })
            );

        // Crear la solicitud simulada
        $request = new Request([
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'token' => 'valid_token',
        ]);

        // Crear una instancia del controlador
        $authController = new AuthController();

        // Llamar al método resetPassword
        $response = $authController->resetPassword($request);

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Contraseña restablecida correctamente', $responseData['message']);
    }


    public function testIsValidTokenSuccess()
    {
        // Crear un usuario de prueba
        $user = User::factory()->create(['email' => 'test@test.com']);

        // Mockear el Password broker directamente
        Password::shouldReceive('broker')
            ->andReturnSelf();

        Password::shouldReceive('tokenExists')
            ->once()
            ->with(Mockery::type(User::class), 'valid_token')
            ->andReturn(true);

        // Crear la solicitud simulada
        $request = new Request([
            'email' => 'test@test.com',
            'token' => 'valid_token',
        ]);

        // Crear una instancia del controlador
        $authController = new AuthController();

        // Llamar al método isValidTokenResetPassword
        $response = $authController->isValidTokenResetPassword($request);

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $this->assertEquals('Token válido', $response->getData(true)['message']);
    }


    public function testIsValidTokenInvalidToken()
    {
        // Crear un usuario de prueba
        $user = User::factory()->create(['email' => 'test@test.com']);

        // Mockear el Password broker directamente
        Password::shouldReceive('broker')
            ->andReturnSelf();

        Password::shouldReceive('tokenExists')
            ->once()
            ->with(Mockery::type(User::class), 'invalid_token')
            ->andReturn(false);

        // Crear la solicitud simulada
        $request = new Request([
            'email' => 'test@test.com',
            'token' => 'invalid_token',
        ]);

        // Crear una instancia del controlador
        $authController = new AuthController();

        // Llamar al método isValidTokenResetPassword
        $response = $authController->isValidTokenResetPassword($request);

        // Validar la respuesta
        $this->assertEquals(404, $response->status());
        $this->assertEquals('Token no válido', $response->getData(true)['error']);
    }

    public function testIsValidTokenUserNotFound()
    {

        // Crear la solicitud simulada
        $request = new Request([
            'email' => 'nonexistent@test.com',
            'token' => 'valid_token',
        ]);

        // Crear una instancia del controlador
        $authController = new AuthController();

        // Llamar al método isValidTokenResetPassword
        $response = $authController->isValidTokenResetPassword($request);

        // Validar la respuesta
        $this->assertEquals(404, $response->status());
        $this->assertEquals('Usuario no encontrado', $response->getData(true)['error']);
    }

    public function testIsValidTokenInvalidEmail()
    {
        // Crear la solicitud simulada
        $request = new Request([
            'email' => 'invalid_email',
            'token' => 'valid_token',
        ]);

        // Crear una instancia del controlador
        $authController = new AuthController();

        // Llamar al método isValidTokenResetPassword
        $response = $authController->isValidTokenResetPassword($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals('The email field must be a valid email address.', $responseData['errors']['email'][0]);
    }

    public function testIsValidTokenEmptyToken()
    {
        // Crear la solicitud simulada
        $request = new Request([
            'email' => 'test@test.com',
            'token' => '',
        ]);

        // Crear una instancia del controlador
        $authController = new AuthController();

        // Llamar al método isValidTokenResetPassword
        $response = $authController->isValidTokenResetPassword($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals('The token field is required.', $responseData['errors']['token'][0]);
    }

    public function testIsValidTokenEmptyEmail()
    {
        // Crear la solicitud simulada
        $request = new Request([
            'email' => '',
            'token' => 'valid_token',
        ]);

        // Crear una instancia del controlador
        $authController = new AuthController();

        // Llamar al método isValidTokenResetPassword
        $response = $authController->isValidTokenResetPassword($request);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals('The email field is required.', $responseData['errors']['email'][0]);
    }









}
