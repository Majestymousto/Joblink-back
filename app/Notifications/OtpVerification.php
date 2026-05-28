<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpVerification extends Notification
{
    use Queueable;

    public function __construct(private string $otpCode) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Vérification de votre compte JobLink')
            ->view('emails.otp-verification', [
                'name' => $notifiable->name,
                'otpCode' => $this->otpCode,
            ]);
    }
}
