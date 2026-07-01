<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Reminder;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Collection;

class NotificationService
{
    use LogsActivity;

    /**
     * Create a new notification.
     */
    /**
     * Create a new notification.
     */
    public function create(
        int $userId,
        string $type,
        string $title,
        ?string $description = null,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'data' => $data,
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * Get notifications for a specific user.
     */
    public function getForUser(
        int $userId,
        int $limit = 10,
        bool $unreadOnly = false
    ): Collection {
        $query = Notification::forUser($userId)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get all notifications for a user with pagination.
     */
    public function getAllForUser(int $userId, int $perPage = 20)
    {
        return Notification::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (! $notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->count();
    }

    /**
     * Delete a notification.
     */
    public function delete(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (! $notification) {
            return false;
        }

        return $notification->delete();
    }

    /**
     * Delete all notifications for a user.
     */
    public function deleteAllForUser(int $userId): int
    {
        return Notification::forUser($userId)->delete();
    }

    /**
     * Create a success notification.
     */
    public function success(
        int $userId,
        string $title,
        ?string $description = null,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        return $this->create($userId, 'success', $title, $description, $data, $actionUrl);
    }

    /**
     * Create an info notification.
     */
    public function info(
        int $userId,
        string $title,
        ?string $description = null,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        return $this->create($userId, 'info', $title, $description, $data, $actionUrl);
    }

    /**
     * Create a warning notification.
     */
    public function warning(
        int $userId,
        string $title,
        ?string $description = null,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        return $this->create($userId, 'warning', $title, $description, $data, $actionUrl);
    }

    /**
     * Create an error notification.
     */
    public function error(
        int $userId,
        string $title,
        ?string $description = null,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        return $this->create($userId, 'error', $title, $description, $data, $actionUrl);
    }

    /**
     * Send reminder notification to assigned user and relevant collaborators.
     */
    public function sendReminderNotification(Reminder $reminder): void
    {
        $recipients = $this->getReminderRecipients($reminder);

        foreach ($recipients as $user) {
            $this->create(
                $user->id,
                'reminder',
                $this->getReminderTitle($reminder),
                $reminder->note ?? 'Reminder is due',
                [
                    'reminder_id' => $reminder->id,
                    'reminder_type' => $reminder->type,
                    'priority' => $reminder->priority,
                    'remind_at' => $reminder->remind_at->toIso8601String(),
                ],
                $this->getReminderActionUrl($reminder)
            );
        }

        $this->logActivity('reminder_notification_sent', 'Sent reminder notification to '.$recipients->count().' users', $reminder);
    }

    /**
     * Get all recipients for a reminder notification.
     */
    protected function getReminderRecipients(Reminder $reminder): Collection
    {
        $recipients = collect();

        // Always include assigned user
        if ($reminder->assigned_to) {
            $recipients->push($reminder->assignedTo);
        }

        // If reminder is for a deal, include owner and collaborators
        if ($reminder->remindable_type === 'App\\Models\\Deal') {
            $deal = $reminder->remindable;

            // Include deal owner
            if ($deal->assigned_to && $deal->assigned_to !== $reminder->assigned_to) {
                $recipients->push($deal->assignedToUser);
            }

            // Include collaborators
            foreach ($deal->collaborators as $collaborator) {
                if (! $recipients->contains('id', $collaborator->id)) {
                    $recipients->push($collaborator);
                }
            }
        }

        return $recipients->unique('id');
    }

    /**
     * Get reminder title based on type.
     */
    protected function getReminderTitle(Reminder $reminder): string
    {
        return match ($reminder->type) {
            'follow_up_call' => '📞 Follow-up Call Reminder',
            'meeting' => '📅 Meeting Reminder',
            'proposal_submission' => '📝 Proposal Submission Due',
            'closing_date' => '🎯 Deal Closing Date',
            'task_due' => '✅ Task Due',
            'customer_follow_up' => '👤 Customer Follow-up',
            default => '🔔 Reminder',
        };
    }

    /**
     * Get action URL based on remindable type.
     */
    protected function getReminderActionUrl(Reminder $reminder): string
    {
        $remindable = $reminder->remindable;

        return match ($reminder->remindable_type) {
            'App\\Models\\Deal' => '/admin/deals/'.$remindable->id,
            'App\\Models\\Task' => '/admin/tasks/'.$remindable->id,
            default => '/admin/reminders',
        };
    }
}
