<?php

namespace App\Notifications;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TareaCerradaPorAdminNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Tarea $tarea,
        protected User $admin
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tarea finalizada por administración')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('La tarea fue cerrada por administración.')
            ->line('Tarea: ' . $this->tarea->titulo)
            ->line('Cerrada por: ' . $this->admin->name)
            ->line('Fecha de cierre: ' . now()->format('d/m/Y H:i'))
            ->action('Ver tarea', url('/tareas?tarea_id=' . $this->tarea->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'module' => 'tareas',
            'title' => 'Tarea finalizada',
            'message' => $this->admin->name . ' cerró la tarea: ' . $this->tarea->titulo,
            'task_id' => $this->tarea->id,
            'url' => url('/tareas?tarea_id=' . $this->tarea->id),
            'actor' => $this->admin->name,
            'type' => 'cierre_admin',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
