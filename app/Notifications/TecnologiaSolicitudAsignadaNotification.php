<?php

namespace App\Notifications;

use App\Models\TecnologiaSolicitud;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TecnologiaSolicitudAsignadaNotification extends Notification
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
            ->subject($this->solicitud->ticket_codigo . ' - Nueva solicitud asignada')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Se te asigno una nueva solicitud del modulo de Tecnologia.')
            ->line('Ticket: ' . $this->solicitud->ticket_codigo)
            ->line('Tipo: ' . ucfirst($this->solicitud->tipo))
            ->line('Titulo: ' . $this->solicitud->titulo)
            ->line('Solicitada por: ' . $this->actor->name)
            ->action('Ver solicitud', url('/tecnologia/solicitudes?solicitud_id=' . $this->solicitud->id))
            ->line('Revisa la solicitud para iniciar gestion y seguimiento.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'module' => 'tecnologia',
            'title' => 'Nueva solicitud de Tecnologia',
            'message' => $this->actor->name . ' te asigno el ticket ' . $this->solicitud->ticket_codigo . ': ' . $this->solicitud->titulo,
            'url' => url('/tecnologia/solicitudes?solicitud_id=' . $this->solicitud->id),
            'type' => 'solicitud_asignada',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
