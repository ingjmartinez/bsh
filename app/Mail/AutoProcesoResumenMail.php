<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutoProcesoResumenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        $sistema = strtoupper((string) ($this->data['sistema'] ?? 'SISTEMA'));
        $fecha = (string) ($this->data['fecha'] ?? now()->format('Y-m-d'));

        return new Envelope(
            subject: "Resumen auto proceso {$sistema} - {$fecha}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auto-proceso-resumen',
        );
    }
}
