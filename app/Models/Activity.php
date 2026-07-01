<?php

namespace App\Models;

use App\Observers\ActivityObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(ActivityObserver::class)]
class Activity extends BaseModel
{
    use SoftDeletes;

    public bool $disableActivityLogging = true;

    protected $fillable = [
        'activityable_type',
        'activityable_id',
        'type',
        'subject',
        'description',
        'duration_minutes',
        'scheduled_at',
        'completed_at',
        'outcome',
        'performed_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function activityable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function performedByUser()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope a query to only include activities visible to the given user.
     */
    public function scopeVisibleTo($query, ?User $user)
    {
        if (! $user) {
            return $query->whereRaw('1=0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where('performed_by', $user->id);
    }
}