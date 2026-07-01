<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReminderController extends Controller
{
    public function __construct(
        protected ReminderService $reminderService
    ) {}

    /**
     * Display a listing of reminders.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $filters = $request->only(['status', 'type', 'priority', 'search', 'category']);

        $query = Reminder::query()
            ->with(['remindable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Task::class => ['taskable'],
                ]);
            }, 'assignedTo', 'createdBy']);

        // Filter by user's assigned reminders or all if super admin
        if (! $user->isSuperAdmin()) {
            $query->where('assigned_to', $user->id);
        }

        // Apply filters
        if ($filters['status'] ?? null) {
            $query->where('status', $filters['status']);
        }

        if ($filters['type'] ?? null) {
            $query->where('type', $filters['type']);
        }

        if ($filters['priority'] ?? null) {
            $query->where('priority', $filters['priority']);
        }

        if ($filters['search'] ?? null) {
            $query->where('note', 'like', '%'.$filters['search'].'%');
        }

        if ($filters['category'] ?? null) {
            match ($filters['category']) {
                'tasks' => $query->where('remindable_type', \App\Models\Task::class),
                'deals' => $query->where('remindable_type', \App\Models\Deal::class),
                'others' => $query->whereNotIn('remindable_type', [\App\Models\Task::class, \App\Models\Deal::class]),
                default => null,
            };
        }

        $reminders = $query->orderBy('remind_at', 'asc')
            ->paginate(20)
            ->withQueryString();

        // Get summary counts
        $targetUserId = $user->isSuperAdmin() ? null : $user->id;
        $todayCount = $this->reminderService->getTodaysReminders($targetUserId)->count();
        $overdueCount = $this->reminderService->getOverdueReminders($targetUserId)->count();
        $upcomingCount = $this->reminderService->getUpcomingReminders($targetUserId, 7)->count();

        return Inertia::render('Admin/Reminders/Index', [
            'reminders' => $reminders,
            'filters' => $filters,
            'summary' => [
                'today' => $todayCount,
                'overdue' => $overdueCount,
                'upcoming' => $upcomingCount,
            ],
        ]);
    }

    /**
     * Store a newly created reminder.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'remindable_type' => 'required|string',
            'remindable_id' => 'required|integer',
            'remind_at' => 'required|date',
            'type' => 'required|in:follow_up_call,meeting,proposal_submission,closing_date,task_due,customer_follow_up,custom',
            'priority' => 'required|in:low,medium,high,urgent',
            'note' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $reminder = $this->reminderService->createReminder($validated);

        return back()->with('success', 'Reminder created successfully');
    }

    /**
     * Update the specified reminder.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'remind_at' => 'sometimes|date',
            'type' => 'sometimes|in:follow_up_call,meeting,proposal_submission,closing_date,task_due,customer_follow_up,custom',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'note' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $reminder = $this->reminderService->updateReminder($id, $validated);

        return back()->with('success', 'Reminder updated successfully');
    }

    /**
     * Remove the specified reminder.
     */
    public function destroy(int $id)
    {
        Reminder::findOrFail($id)->delete();

        return back()->with('success', 'Reminder deleted successfully');
    }

    /**
     * Snooze a reminder.
     */
    public function snooze(Request $request, int $id)
    {
        $validated = $request->validate([
            'minutes' => 'required|integer|min:5|max:1440',
        ]);

        $this->reminderService->snoozeReminder($id, $validated['minutes']);

        return back()->with('success', 'Reminder snoozed');
    }

    /**
     * Dismiss a reminder.
     */
    public function dismiss(int $id)
    {
        $this->reminderService->dismissReminder($id);

        return back()->with('success', 'Reminder dismissed');
    }
}
