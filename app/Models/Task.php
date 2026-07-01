<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends BaseModel implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'taskable_type',
        'taskable_id',
        'priority',
        'status',
        'due_date',
        'reminder_at',
        'completed_at',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reminder_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function taskable()
    {
        return $this->morphTo();
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class , 'assigned_to');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class , 'created_by');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class , 'activityable');
    }

    public function reminders()
    {
        return $this->morphMany(Reminder::class , 'remindable');
    }

    /**
     * Scope a query to only include tasks visible to the given user.
     */
    public function scopeVisibleTo($query, ?User $user)
    {
        if (!$user) {
            return $query->whereRaw('1=0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isCustomer()) {
            return $query->where('taskable_type', User::class)
                ->where('taskable_id', $user->id);
        }

        // Admins/Staff see their own tasks and unassigned tasks
        return $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
                ->orWhereNull('assigned_to');
        });
    }
}