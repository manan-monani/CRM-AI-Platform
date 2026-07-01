<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminder extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'remindable_type',
        'remindable_id',
        'remind_at',
        'type',
        'priority',
        'note',
        'status',
        'notified_at',
        'snoozed_until',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'notified_at' => 'datetime',
        'snoozed_until' => 'datetime',
    ];

    /**
     * Get the parent remindable model (deal, task, or customer).
     */
    public function remindable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user assigned to this reminder.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this reminder.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include pending reminders.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include due reminders.
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query->where('remind_at', '<=', now())
            ->where('status', 'pending')
            ->whereNull('snoozed_until');
    }

    /**
     * Scope a query to only include overdue reminders.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('remind_at', '<', now())
            ->where('status', 'pending');
    }

    /**
     * Scope a query to filter reminders for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to include upcoming reminders (next 7 days).
     */
    public function scopeUpcoming(Builder $query, int $days = 7): Builder
    {
        return $query->where('remind_at', '>=', now())
            ->where('remind_at', '<=', now()->addDays($days))
            ->where('status', 'pending');
    }

    /**
     * Mark this reminder as sent.
     */
    public function markAsSent(): bool
    {
        return $this->update([
            'status' => 'sent',
            'notified_at' => now(),
        ]);
    }

    /**
     * Snooze this reminder for a specified number of minutes.
     */
    public function snooze(int $minutes): bool
    {
        return $this->update([
            'status' => 'snoozed',
            'snoozed_until' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Dismiss this reminder.
     */
    public function dismiss(): bool
    {
        return $this->update(['status' => 'dismissed']);
    }

    /**
     * Check if reminder is snoozed and time has passed.
     */
    public function isSnoozed(): bool
    {
        return $this->status === 'snoozed'
            && $this->snoozed_until
            && $this->snoozed_until->isFuture();
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'gray',
            default => 'gray',
        };
    }
}
