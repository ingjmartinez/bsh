<?php

namespace App\Notifications;

use App\Models\TecnologiaSolicitud;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TecnologiaSolicitudCierreSolicitadoNotification extends Notification
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
            ->subject($this->solicitud->ticket_codigo . ' - Solicitud de cierre')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('El responsable del ticket solicito su cierre.')
            ->line('Ticket: ' . $this->solicitud->ticket_codigo)
            ->line('Titulo: ' . $this->solicitud->titulo)
            ->line('Responsable: ' . $this->actor->name)
            ->line('Progreso reportado: ' . $this->solicitud->progreso . '%')
            ->action('Revisar ticket', url('/tecnologia/solicitudes?solicitud_id=' . $this->solicitud->id))
            ->line('Valida la solicitud y finaliza el ticket si todo esta correcto.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'module' => 'tecnologia',
            'title' => 'Solicitud de cierre pendiente',
            'message' => $this->actor->name . ' solicito cerrar el ticket ' . $this->solicitud->ticket_codigo . '.',
            'url' => url('/tecnologia/solicitudes?solicitud_id=' . $this->solicitud->id),
            'type' => 'solicitud_cierre',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
