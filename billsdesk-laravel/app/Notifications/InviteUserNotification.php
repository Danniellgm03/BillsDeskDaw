<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Invitation;

class InviteUserNotification extends Notification
{
    use Queueable;

    protected $invitation;


    /**
     * Create a new notification instance.
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = env('FRONTEND_URL') . "/register/invite?token={$this->invitation->token}";


        return (new MailMessage)
            ->subject('You are invited to join our platform')
            ->greeting("Hello!")
            ->line("You have been invited to join our platform.")
            ->action('Register', $url)
            ->line("Click the button above to create your account.")
            ->markdown('emails.invite', ['url' => $url]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'token' => $this->invitation->token,
        ];
    }
}
