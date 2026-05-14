<?php

namespace App\Notifications;

use App\Models\ServicioGeneralRequerimiento;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServicioGeneralRequerimientoAsignadaNotification extends Notification
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
            ->subject($this->requerimiento->ticket_codigo . ' - Nuevo requerimiento asignado')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Se te asigno un nuevo requerimiento del modulo de Servicios Generales.')
            ->line('Ticket: ' . $this->requerimiento->ticket_codigo)
            ->line('Tipo: ' . $this->requerimiento->tipo_label)
            ->line('Titulo: ' . $this->requerimiento->titulo)
            ->line('Solicitada por: ' . $this->actor->name)
            ->action('Ver requerimiento', url('/servicios-generales/requerimientos?requerimiento_id=' . $this->requerimiento->id))
            ->line('Revisa el requerimiento para iniciar gestion y seguimiento.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'module' => 'servicios_generales',
            'title' => 'Nuevo requerimiento de Servicios Generales',
            'message' => $this->actor->name . ' te asigno el ticket ' . $this->requerimiento->ticket_codigo . ': ' . $this->requerimiento->titulo,
            'url' => url('/servicios-generales/requerimientos?requerimiento_id=' . $this->requerimiento->id),
            'type' => 'requerimiento_asignado',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
