<?php

namespace App\Notifications;

use App\Models\ServicioGeneralRequerimiento;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServicioGeneralRequerimientoCierreSolicitadoNotification extends Notification
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
            ->subject($this->requerimiento->ticket_codigo . ' - Solicitud de cierre')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('El responsable del ticket solicito su cierre.')
            ->line('Ticket: ' . $this->requerimiento->ticket_codigo)
            ->line('Titulo: ' . $this->requerimiento->titulo)
            ->line('Responsable: ' . $this->actor->name)
            ->line('Progreso reportado: ' . $this->requerimiento->progreso . '%')
            ->action('Revisar ticket', url('/servicios-generales/requerimientos?requerimiento_id=' . $this->requerimiento->id))
            ->line('Valida la solicitud y finaliza el ticket si todo esta correcto.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'module' => 'servicios_generales',
            'title' => 'Solicitud de cierre pendiente',
            'message' => $this->actor->name . ' solicito cerrar el ticket ' . $this->requerimiento->ticket_codigo . '.',
            'url' => url('/servicios-generales/requerimientos?requerimiento_id=' . $this->requerimiento->id),
            'type' => 'requerimiento_cierre',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
