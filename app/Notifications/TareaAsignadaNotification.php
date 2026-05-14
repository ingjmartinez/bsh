<?php

namespace App\Notifications;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TareaAsignadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Tarea $tarea,
        protected User $asignador
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nueva tarea asignada en CRM')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Se te asignó una nueva tarea en el sistema.')
            ->line('Tarea: ' . $this->tarea->titulo)
            ->line('Asignada por: ' . $this->asignador->name)
            ->line('Fecha fin: ' . optional($this->tarea->fecha_fin)->format('d/m/Y'))
            ->action('Ver tarea', url('/tareas'))
            ->line('Por favor revisa el módulo de tareas para más detalles.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'module' => 'tareas',
            'title' => 'Nueva tarea asignada',
            'message' => $this->asignador->name . ' te asignó la tarea: ' . $this->tarea->titulo,
            'task_id' => $this->tarea->id,
            'url' => url('/tareas?tarea_id=' . $this->tarea->id),
            'actor' => $this->asignador->name,
            'type' => 'asignacion',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
