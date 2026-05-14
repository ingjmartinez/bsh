<?php

namespace App\Notifications;

use App\Models\TecnologiaSolicitud;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TecnologiaSolicitudActualizadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected TecnologiaSolicitud $solicitud,
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
            ->subject($this->solicitud->ticket_codigo . ' - Solicitud actualizada')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('La solicitud de Tecnologia fue actualizada.')
            ->line('Ticket: ' . $this->solicitud->ticket_codigo)
            ->line('Titulo: ' . $this->solicitud->titulo)
            ->line('Actualizada por: ' . $this->actor->name);

        if (!empty($this->cambios['estado'])) {
            $mail->line('Nuevo estado: ' . ucfirst(str_replace('_', ' ', $this->solicitud->estado)));
        }

        if (!empty($this->cambios['asignado'])) {
            $mail->line('Nuevo responsable: ' . ($this->solicitud->asignado->name ?? 'Sin asignar'));
        }

        if (!empty($this->cambios['detalle_solucion']) && !empty($this->solicitud->detalle_solucion)) {
            $mail->line('Se agregaron notas de gestion o solucion al ticket.');
        }

        if (!empty($this->cambios['progreso']) && $this->solicitud->tipo === 'desarrollo') {
            $mail->line('Nuevo progreso reportado: ' . $this->solicitud->progreso . '%.');
        }

        return $mail
            ->action('Ver solicitud', url('/tecnologia/solicitudes?solicitud_id=' . $this->solicitud->id))
            ->line('Puedes entrar al modulo para revisar el detalle completo.');
    }

    public function toArray(object $notifiable): array
    {
        $partes = [];

        if (!empty($this->cambios['estado'])) {
            $partes[] = 'estado ' . ucfirst(str_replace('_', ' ', $this->solicitud->estado));
        }

        if (!empty($this->cambios['asignado'])) {
            $partes[] = 'responsable ' . ($this->solicitud->asignado->name ?? 'Sin asignar');
        }

        if (!empty($this->cambios['detalle_solucion'])) {
            $partes[] = 'detalle de solucion actualizado';
        }

        if (!empty($this->cambios['progreso'])) {
            $partes[] = 'progreso ' . $this->solicitud->progreso . '%';
        }

        $detalle = empty($partes) ? 'Se actualizo la solicitud.' : 'Cambios: ' . implode(', ', $partes) . '.';

        return [
            'module' => 'tecnologia',
            'title' => 'Solicitud de Tecnologia actualizada',
            'message' => $this->actor->name . ' actualizo el ticket ' . $this->solicitud->ticket_codigo . '. ' . $detalle,
            'url' => url('/tecnologia/solicitudes?solicitud_id=' . $this->solicitud->id),
            'type' => 'solicitud_actualizada',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
