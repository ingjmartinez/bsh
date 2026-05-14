<?php

namespace App\Notifications;

use App\Models\ServicioGeneralRequerimiento;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServicioGeneralRequerimientoFinalizadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ServicioGeneralRequerimiento $requerimiento,
        protected User $actor
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->requerimiento->ticket_codigo . ' - Ticket finalizado')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('El requerimiento de Servicios Generales fue finalizado.')
            ->line('Ticket: ' . $this->requerimiento->ticket_codigo)
            ->line('Titulo: ' . $this->requerimiento->titulo)
            ->line('Finalizado por: ' . $this->actor->name)
            ->action('Ver ticket', url('/servicios-generales/requerimientos?requerimiento_id=' . $this->requerimiento->id))
            ->line('El ticket fue validado como completado correctamente.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'module' => 'servicios_generales',
            'title' => 'Ticket finalizado',
            'message' => $this->actor->name . ' finalizo el ticket ' . $this->requerimiento->ticket_codigo . '.',
            'url' => url('/servicios-generales/requerimientos?requerimiento_id=' . $this->requerimiento->id),
            'type' => 'requerimiento_finalizado',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
