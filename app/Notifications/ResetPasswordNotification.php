<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $token
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $broker = config('auth.defaults.passwords', 'users');
        $expiresMinutes = (int) config("auth.passwords.{$broker}.expire", 60);

        $resetUrl = URL::route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], true);

        return (new MailMessage)
            ->subject('Redefina sua senha no Papirar Concursos')
            ->view('emails.auth.reset-password', [
                'user' => $notifiable,
                'resetUrl' => $resetUrl,
                'expiresMinutes' => $expiresMinutes,
            ]);
    }
}
