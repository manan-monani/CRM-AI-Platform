<?php

namespace App\Notifications;

use App\Channels\SystemNotificationChannel;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Task $task, public string $action = 'created')
    {
    //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [SystemNotificationChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toSystemNotification(object $notifiable): array
    {
        $title = match ($this->action) {
                'updated' => 'Task Updated: ' . ($this->task->title ?? 'No Title'),
                'deleted' => 'Task Deleted: ' . ($this->task->title ?? 'No Title'),
                'media_deleted' => 'Attachment Deleted: ' . ($this->task->title ?? 'No Title'),
                default => 'New Task: ' . ($this->task->title ?? 'No Title'),
            };

        return [
            'type' => 'info',
            'title' => $title,
            'description' => $this->task->description,
            'data' => [
                'task_id' => $this->task->id,
                'user_id' => $this->task->assigned_to,
                'user_name' => $this->task->assignedToUser->name ?? 'Unassigned',
                'priority' => $this->task->priority,
                'status' => $this->task->status,
                'sound' => 'notification',
                'action' => $this->action,
            ],
            'action_url' => route('admin.tasks.show', $this->task->id),
        ];
    }
}