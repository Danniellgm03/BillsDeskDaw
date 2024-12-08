<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Invitation;
use App\Notifications\InviteUserNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InviteUserNotificationTest extends TestCase
{

    use RefreshDatabase;


    public function testInviteUserNotificationIsSent(): void
    {
        // Simular una notificación
        Notification::fake();

        // Crear una invitación simulada
        $invitation = Invitation::factory()->create([
            'email' => 'test@example.com',
            'token' => 'fake-token',
        ]);

        // Crear un usuario simulado
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Enviar la notificación
        $user->notify(new InviteUserNotification($invitation));

        // Verificar que la notificación fue enviada al usuario correcto
        Notification::assertSentTo(
            [$user],
            InviteUserNotification::class,
            function ($notification, $channels) use ($invitation) {
                return $notification->toArray($notification)['token'] === $invitation->token;
            }
        );
    }

    public function testInviteUserNotificationEmailContent(): void
    {
        // Crear una invitación simulada
        $invitation = Invitation::factory()->create([
            'email' => 'test@example.com',
            'token' => 'fake-token',
        ]);

        // Crear la notificación
        $notification = new InviteUserNotification($invitation);

        // Generar el contenido del correo
        $user = User::factory()->create(['email' => 'test@example.com']);
        $mailData = $notification->toMail($user);

        // Verificar el asunto
        $this->assertEquals('You are invited to join our platform', $mailData->subject);

        // Verificar que el enlace tiene el token correcto
        $expectedUrl = env('FRONTEND_URL') . "/register/invite?token=fake-token";
        $this->assertStringContainsString($expectedUrl, $mailData->actionUrl);

        // Verificar el contenido del mensaje
        $this->assertStringContainsString('You have been invited to join our platform.', $mailData->introLines[0]);
        $this->assertStringContainsString('Click the button above to create your account.', $mailData->outroLines[0]);
    }
}
