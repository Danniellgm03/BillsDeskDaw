<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\ForgotPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForgotPasswordNotificationTest extends TestCase
{

    use RefreshDatabase;

    public function testForgotPasswordNotificationIsSent(): void
    {
        // Simular una notificación
        Notification::fake();

        // Crear un usuario simulado
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Token simulado
        $token = 'fake-token';

        // Enviar la notificación
        $user->notify(new ForgotPasswordNotification($token));

        // Verificar que la notificación fue enviada al usuario
        Notification::assertSentTo(
            [$user],
            ForgotPasswordNotification::class,
            function ($notification, $channels) use ($token) {
                return $notification->getToken() === $token;
            }
        );

    }

    public function testForgotPasswordNotificationEmailContent(): void
    {
        // Simular un usuario y un token
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'fake-token';

        // Crear la notificación
        $notification = new ForgotPasswordNotification($token);

        // Generar el contenido del correo
        $mailData = $notification->toMail($user);

        // Verificar el asunto
        $this->assertEquals('Restablecimiento de contraseña', $mailData->subject);

        // Verificar que el enlace tiene el token y el correo
        $expectedUrl = env('FRONTEND_URL') . "/reset-password?token={$token}&email={$user->email}";
        $this->assertStringContainsString($expectedUrl, $mailData->actionUrl);

        // Verificar el contenido del mensaje
        $this->assertStringContainsString('Recibiste este correo porque solicitaste restablecer tu contraseña.', $mailData->introLines[0]);
        $this->assertStringContainsString('Si no realizaste esta solicitud, puedes ignorar este mensaje.', $mailData->outroLines[0]);
    }

}
