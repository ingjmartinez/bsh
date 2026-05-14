<?php

namespace App\Notifications;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TareaSolicitudCierreNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Tarea $tarea,
        protected User $solicitante
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'module' => 'tareas',
            'title' => 'Solicitud de cierre de tarea',
            'message' => $this->solicitante->name . ' solicitó cerrar la tarea: ' . $this->tarea->titulo,
            'task_id' => $this->tarea->id,
            'url' => url('/tareas?tarea_id=' . $this->tarea->id),
            'actor' => $this->solicitante->name,
            'type' => 'solicitud_cierre',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
