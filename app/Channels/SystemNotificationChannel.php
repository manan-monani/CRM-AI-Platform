<?php

namespace App\Channels;

use App\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;

class SystemNotificationChannel
{
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSystemNotification')) {
            throw new \RuntimeException('Notification does not have toSystemNotification method.');
        }

        $data = $notification->toSystemNotification($notifiable);

        NotificationModel::create([
            'user_id' => $notifiable->id,
            'type' => $data['type'] ?? 'info',
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'data' => $data['data'] ?? [],
            'action_url' => $data['action_url'] ?? null,
            'read_at' => null,
        ]);
    }
}
