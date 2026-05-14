<?php

namespace App\Notifications;

use App\Models\ServicioGeneralRequerimiento;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServicioGeneralRequerimientoActualizadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ServicioGeneralRequerimiento $requerimiento,
        protected User $actor,
        protected array $cambios = []
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->requerimiento->ticket_codigo . ' - Requerimiento actualizado')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('El requerimiento de Servicios Generales fue actualizado.')
            ->line('Ticket: ' . $this->requerimiento->ticket_codigo)
            ->line('Titulo: ' . $this->requerimiento->titulo)
            ->line('Actualizado por: ' . $this->actor->name);

        if (!empty($this->cambios['estado'])) {
            $mail->line('Nuevo estado: ' . ucfirst(str_replace('_', ' ', $this->requerimiento->estado)));
        }

        if (!empty($this->cambios['asignado'])) {
            $mail->line('Nuevo responsable: ' . ($this->requerimiento->asignado->name ?? 'Sin asignar'));
        }

        if (!empty($this->cambios['detalle_solucion']) && !empty($this->requerimiento->detalle_solucion)) {
            $mail->line('Se agregaron notas de gestion o solucion al ticket.');
        }

        if (!empty($this->cambios['progreso'])) {
            $mail->line('Nuevo progreso reportado: ' . $this->requerimiento->progreso . '%.');
        }

        return $mail
            ->action('Ver requerimiento', url('/servicios-generales/requerimientos?requerimiento_id=' . $this->requerimiento->id))
            ->line('Puedes entrar al modulo para revisar el detalle completo.');
    }

    public function toArray(object $notifiable): array
    {
        $partes = [];

        if (!empty($this->cambios['estado'])) {
            $partes[] = 'estado ' . ucfirst(str_replace('_', ' ', $this->requerimiento->estado));
        }

        if (!empty($this->cambios['asignado'])) {
            $partes[] = 'responsable ' . ($this->requerimiento->asignado->name ?? 'Sin asignar');
        }

        if (!empty($this->cambios['detalle_solucion'])) {
            $partes[] = 'detalle de solucion actualizado';
        }

        if (!empty($this->cambios['progreso'])) {
            $partes[] = 'progreso ' . $this->requerimiento->progreso . '%';
        }

        $detalle = empty($partes) ? 'Se actualizo el requerimiento.' : 'Cambios: ' . implode(', ', $partes) . '.';

        return [
            'module' => 'servicios_generales',
            'title' => 'Requerimiento de Servicios Generales actualizado',
            'message' => $this->actor->name . ' actualizo el ticket ' . $this->requerimiento->ticket_codigo . '. ' . $detalle,
            'url' => url('/servicios-generales/requerimientos?requerimiento_id=' . $this->requerimiento->id),
            'type' => 'requerimiento_actualizado',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
