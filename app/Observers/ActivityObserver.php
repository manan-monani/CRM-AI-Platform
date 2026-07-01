<?php

namespace App\Observers;

use App\Enums\UserType;
use App\Models\Activity;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Notification;

class ActivityObserver
{
    /**
     * Handle the Activity "created" event.
     */
    public function created(Activity $activity): void
    {
        $this->sendNotification($activity, 'created');
    }

    /**
     * Handle the Activity "updated" event.
     */
    public function updated(Activity $activity): void
    {
        $this->sendNotification($activity, 'updated');
    }

    /**
     * Handle the Activity "deleted" event.
     */
    public function deleted(Activity $activity): void
    {
        $this->sendNotification($activity, 'deleted');
    }

    protected function sendNotification(Activity $activity, string $action): void
    {
        // Get the user who performed the activity
        $performer = auth()->user() ?? $activity->user;

        // Send notification to all Super Admins
        $superAdmins = User::where('type', UserType::SUPER_ADMIN)->get();

        // Also notify the deal/lead owner if applicable
        $owner = null;
        if ($activity->activityable && $activity->activityable instanceof \App\Models\Deal) {
            $owner = $activity->activityable->assignedToUser;
        }
        elseif ($activity->activityable && $activity->activityable instanceof \App\Models\Lead) {
            $owner = $activity->activityable->assignedToUser;
        }

        $recipients = $superAdmins;
        if ($owner && !$recipients->contains($owner->id)) {
            $recipients->push($owner);
        }

        // Remove the performer from recipients to avoid self-notification
        // Remove the performer from recipients to avoid self-notification
        // BUT allow Super Admins to receive their own notifications for verification
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
            Notification::send($recipients, new ActivityNotification($activity, $action));
        }
    }
}