<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityService
{
    public function logActivity(array $data, Model $activityable, User $user): Activity
    {
        return Activity::create([
            'activityable_type' => get_class($activityable),
            'activityable_id' => $activityable->id,
            'type' => $data['type'],
            'subject' => $data['subject'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'completed_at' => $data['completed_at'] ?? now(),
            'outcome' => $data['outcome'] ?? null,
            'performed_by' => $user->id,
        ]);
    }
}
