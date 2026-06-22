<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContrasenaRestablecida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $usuario,
        public readonly string $nuevaPassword,
        public readonly string $restablecidaPor,
        public readonly string $appUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contraseña restablecida — SIGESI',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contrasena-restablecida',
        );
    }
}
