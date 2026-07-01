<?php

namespace App\Notifications;

use App\Channels\SystemNotificationChannel;
use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActivityNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Activity $activity, public string $action = 'created')
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
                'updated' => 'Activity Updated: ' . ($this->activity->subject ?? 'No Subject'),
                'deleted' => 'Activity Deleted: ' . ($this->activity->subject ?? 'No Subject'),
                default => 'New Activity: ' . ($this->activity->subject ?? 'No Subject'),
            };

        $actionUrl = null;
        if ($this->activity->activityable) {
            if ($this->activity->activityable instanceof \App\Models\Deal) {
                $actionUrl = route('admin.deals.show', $this->activity->activityable->id);
            }
            elseif ($this->activity->activityable instanceof \App\Models\Lead) {
                $actionUrl = route('admin.leads.show', $this->activity->activityable->id);
            }
        }

        return [
            'type' => 'info',
            'title' => $title,
            'description' => $this->activity->description,
            'data' => [
                'activity_id' => $this->activity->id,
                'user_id' => $this->activity->user_id,
                'user_name' => $this->activity->user->name ?? 'Unknown User',
                'type' => $this->activity->type,
                'sound' => 'activity',
                'action' => $this->action,
            ],
            'action_url' => $actionUrl,
        ];
    }
}