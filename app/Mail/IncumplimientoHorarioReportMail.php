<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncumplimientoHorarioReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public array $data,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $agencia = $this->data['agencia'] ?? '-';
        $fecha = $this->data['fecha'] ?? now()->format('d/m/Y');

        return new Envelope(
            subject: "Mini reporte de incumplimiento - Agencia {$agencia} ({$fecha})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.incumplimiento-horario-mini-reporte',
        );
    }
}
