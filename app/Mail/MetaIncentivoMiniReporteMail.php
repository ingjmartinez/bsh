<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MetaIncentivoMiniReporteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data,
    ) {}

    public function envelope(): Envelope
    {
        $coordinador = $this->data['coordinador'] ?? '-';
        // Usar el período (mes+1) si está disponible, sino caer al mes original
        $mes = str_pad((string) ($this->data['periodo_mes'] ?? $this->data['mes'] ?? ''), 2, '0', STR_PAD_LEFT);
        $anio = $this->data['periodo_anio'] ?? $this->data['anio'] ?? now()->format('Y');

        return new Envelope(
            subject: "Mini reporte Meta Incentivo - {$coordinador} ({$mes}/{$anio})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.meta-incentivo-mini-reporte',
        );
    }
}
