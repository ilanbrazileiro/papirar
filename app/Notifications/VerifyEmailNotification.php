<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'auth.verification.verify',
            now()->addHours(24),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            true
        );

        return (new MailMessage)
            ->subject('Confirme seu e-mail no Papirar Concursos')
            ->view('emails.auth.verify-email', [
                'user' => $notifiable,
                'verificationUrl' => $verificationUrl,
                'expiresAt' => now()->addHours(24),
            ]);
    }
}
