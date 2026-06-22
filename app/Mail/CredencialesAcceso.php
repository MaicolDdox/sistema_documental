<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredencialesAcceso extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $usuario,
        public readonly string $passwordTemporal,
        public readonly string $appUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Credenciales de acceso — SIGESI',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.credenciales-acceso',
        );
    }
}
