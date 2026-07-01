<?php

namespace App\Notifications;

use App\Channels\SystemNotificationChannel;
use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DealUpdatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Deal $deal, public string $action)
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
        $updaterName = auth()->user()->name ?? 'System';

        return [
            'type' => 'info',
            'title' => __('Deal :action: :title', ['action' => $this->action, 'title' => $this->deal->title]),
            'description' => __('Deal ":title" was :action by :user.', [
                'title' => $this->deal->title,
                'action' => strtolower($this->action),
                'user' => $updaterName,
            ]),
            'data' => [
                'deal_id' => $this->deal->id,
                'updater_name' => $updaterName,
                'action' => $this->action,
                'sound' => 'activity',
            ],
            'action_url' => route('admin.deals.show', $this->deal->id, false),
        ];
    }
}