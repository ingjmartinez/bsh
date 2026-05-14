<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AsistenciaComparativaCoordinadorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data,
    ) {}

    public function envelope(): Envelope
    {
        $coordinador = $this->data['coordinador'] ?? '-';
        $fecha = $this->data['fecha'] ?? now()->format('d/m/Y');

        return new Envelope(
            subject: "Reporte de incumplimientos por coordinador - {$coordinador} ({$fecha})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.asistencia-comparativa-coordinador',
        );
    }
}
