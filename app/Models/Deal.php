<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Deal extends BaseModel implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title',
        'dealable_type',
        'dealable_id',
        'value',
        'stage',
        'status',
        'probability',
        'expected_close_date',
        'description',
        'assigned_to',
        'created_by',
        'won_at',
        'lost_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expected_close_date' => 'date',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
        'probability' => 'integer',
    ];

    public function dealable()
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

    public function tasks()
    {
        return $this->morphMany(Task::class , 'taskable');
    }

    public function reminders()
    {
        return $this->morphMany(Reminder::class , 'remindable');
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class , 'deal_collaborators')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include deals visible to the given user.
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
            return $query->where('dealable_type', User::class)
                ->where('dealable_id', $user->id);
        }

        // Admins/Staff see their own deals and unassigned deals
        return $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
                ->orWhereNull('assigned_to');
        });
    }
}