<?php

namespace App\Notifications;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TareaComentarioNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Tarea $tarea,
        protected User $autor,
        protected string $comentario
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
            'title' => 'Nuevo comentario en tarea',
            'message' => $this->autor->name . ' comentó en la tarea: ' . $this->tarea->titulo,
            'task_id' => $this->tarea->id,
            'url' => url('/tareas?tarea_id=' . $this->tarea->id),
            'actor' => $this->autor->name,
            'type' => 'comentario',
            'comment' => mb_substr($this->comentario, 0, 140),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
