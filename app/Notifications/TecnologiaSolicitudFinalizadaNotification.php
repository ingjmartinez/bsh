<?php

namespace App\Notifications;

use App\Models\TecnologiaSolicitud;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TecnologiaSolicitudFinalizadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected TecnologiaSolicitud $solicitud,
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
            ->subject($this->solicitud->ticket_codigo . ' - Ticket finalizado')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('La solicitud de Tecnologia fue finalizada.')
            ->line('Ticket: ' . $this->solicitud->ticket_codigo)
            ->line('Titulo: ' . $this->solicitud->titulo)
            ->line('Finalizada por: ' . $this->actor->name)
            ->action('Ver ticket', url('/tecnologia/solicitudes?solicitud_id=' . $this->solicitud->id))
            ->line('El ticket fue validado como completado correctamente.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'module' => 'tecnologia',
            'title' => 'Ticket finalizado',
            'message' => $this->actor->name . ' finalizo el ticket ' . $this->solicitud->ticket_codigo . '.',
            'url' => url('/tecnologia/solicitudes?solicitud_id=' . $this->solicitud->id),
            'type' => 'ticket_finalizado',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
