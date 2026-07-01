<?php

namespace App\Observers;

use App\Enums\UserType;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskNotification;
use Illuminate\Support\Facades\Notification;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $this->sendNotification($task, 'created');
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        $this->sendNotification($task, 'updated');
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        $this->sendNotification($task, 'deleted');
    }

    protected function sendNotification(Task $task, string $action): void
    {
        // Get the user who performed the activity
        $performer = auth()->user() ?? $task->createdByUser;

        // Determine Recipients
        $recipients = collect();

        // 1. Super Admins always get notified
        $superAdmins = User::where('type', UserType::SUPER_ADMIN)->get();
        $recipients = $recipients->merge($superAdmins);

        // 2. Assigned User (if exists)
        if ($task->assignedToUser) {
            $recipients->push($task->assignedToUser);
        }

        // 3. Task Creator (if exists)
        if ($task->createdByUser) {
            $recipients->push($task->createdByUser);
        }

        // Remove duplicates
        $recipients = $recipients->unique('id');

        // Remove the performer from recipients to avoid self-notification
        // Remove the performer from recipients to avoid self-notification
        // BUT allow Super Admins to receive their own notifications for verification if requested
        if ($performer) {
            $recipients = $recipients->reject(function ($user) use ($performer) {
                // If the user is a Super Admin, don't filter them out even if they are the performer
                if ($user->type === UserType::SUPER_ADMIN) {
                    return false;
                }
                return $user->id === $performer->id;
            });
        }

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new TaskNotification($task, $action));
        }
    }
}