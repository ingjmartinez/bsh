<?php

namespace App\Notifications;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TareaProgresoActualizadoNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Tarea $tarea,
        protected User $actor,
        protected int $anterior,
        protected int $nuevo
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
            'title' => 'Progreso actualizado',
            'message' => $this->actor->name . ' actualizó el progreso de "' . $this->tarea->titulo . '" de ' . $this->anterior . '% a ' . $this->nuevo . '%.',
            'task_id' => $this->tarea->id,
            'url' => url('/tareas?tarea_id=' . $this->tarea->id),
            'actor' => $this->actor->name,
            'type' => 'progreso',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
