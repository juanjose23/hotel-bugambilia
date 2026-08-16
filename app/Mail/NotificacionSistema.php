<?php

declare(strict_types=1);

namespace App\Mail;

use App\Notifications\DatosNotificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class NotificacionSistema extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly DatosNotificacion $datos,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->datos->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.notificacion',
        );
    }
}
