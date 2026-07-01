<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Reminder;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ReminderService
{
    use LogsActivity;

    /**
     * Create a new reminder.
     */
    public function createReminder(array $data): Reminder
    {
        $reminder = Reminder::create([
            'remindable_type' => $data['remindable_type'],
            'remindable_id' => $data['remindable_id'],
            'remind_at' => $data['remind_at'],
            'type' => $data['type'],
            'priority' => $data['priority'] ?? 'medium',
            'note' => $data['note'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? auth()->id(),
            'created_by' => auth()->id(),
            'status' => 'pending',
        ]);

        $this->logActivity('reminder_created', "Created reminder: {$reminder->type}", $reminder);

        return $reminder;
    }

    /**
     * Update an existing reminder.
     */
    public function updateReminder(int $id, array $data): Reminder
    {
        $reminder = Reminder::findOrFail($id);

        $reminder->update([
            'remind_at' => $data['remind_at'] ?? $reminder->remind_at,
            'type' => $data['type'] ?? $reminder->type,
            'priority' => $data['priority'] ?? $reminder->priority,
            'note' => $data['note'] ?? $reminder->note,
            'assigned_to' => $data['assigned_to'] ?? $reminder->assigned_to,
        ]);

        $this->logActivity('reminder_updated', "Updated reminder: {$reminder->type}", $reminder);

        return $reminder->fresh();
    }

    /**
     * Get upcoming reminders for a user.
     */
    public function getUpcomingReminders(?int $userId, int $days = 7): Collection
    {
        $query = Reminder::query();

        if ($userId) {
            $query->forUser($userId);
        }

        return $query->upcoming($days)
            ->with(['remindable', 'assignedTo'])
            ->orderBy('remind_at', 'asc')
            ->get();
    }

    /**
     * Get overdue reminders for a user.
     */
    public function getOverdueReminders(?int $userId): Collection
    {
        $query = Reminder::query();

        if ($userId) {
            $query->forUser($userId);
        }

        return $query->overdue()
            ->with(['remindable', 'assignedTo'])
            ->orderBy('remind_at', 'asc')
            ->get();
    }

    /**
     * Get today's reminders for a user.
     */
    public function getTodaysReminders(?int $userId): Collection
    {
        $query = Reminder::query();

        if ($userId) {
            $query->forUser($userId);
        }

        return $query->whereBetween('remind_at', [
            Carbon::today(),
            Carbon::today()->endOfDay(),
        ])
            ->where('status', 'pending')
            ->with(['remindable', 'assignedTo'])
            ->orderBy('remind_at', 'asc')
            ->get();
    }

    /**
     * Snooze a reminder.
     */
    public function snoozeReminder(int $id, int $minutes): Reminder
    {
        $reminder = Reminder::findOrFail($id);
        $reminder->snooze($minutes);

        $this->logActivity('reminder_snoozed', "Snoozed reminder for {$minutes} minutes", $reminder);

        return $reminder->fresh();
    }

    /**
     * Dismiss a reminder.
     */
    public function dismissReminder(int $id): Reminder
    {
        $reminder = Reminder::findOrFail($id);
        $reminder->dismiss();

        $this->logActivity('reminder_dismissed', 'Dismissed reminder', $reminder);

        return $reminder->fresh();
    }

    /**
     * Get all reminders for a deal.
     */
    public function getDealReminders(int $dealId): Collection
    {
        $deal = Deal::findOrFail($dealId);

        return $deal->reminders()
            ->with(['assignedTo', 'createdBy'])
            ->orderBy('remind_at', 'asc')
            ->get();
    }

    /**
     * Get reminders that are due right now.
     */
    public function getDueReminders(): Collection
    {
        return Reminder::due()
            ->with(['remindable', 'assignedTo'])
            ->get();
    }

    /**
     * Mark a reminder as sent.
     */
    public function markReminderAsSent(int $id): Reminder
    {
        $reminder = Reminder::findOrFail($id);
        $reminder->markAsSent();

        return $reminder->fresh();
    }
}
