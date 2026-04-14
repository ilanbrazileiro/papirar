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
            ]
        );

        return (new MailMessage)
            ->subject('Confirme seu e-mail no Papirar')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('Obrigado por se cadastrar no Papirar.')
            ->line('Antes de continuar, confirme seu endereço de e-mail clicando no botão abaixo.')
            ->action('Confirmar e-mail', $verificationUrl)
            ->line('Depois de confirmar o e-mail, você ainda precisará contratar uma assinatura para começar a responder questões.')
            ->line('Se você não criou essa conta, ignore esta mensagem.');
    }
}
