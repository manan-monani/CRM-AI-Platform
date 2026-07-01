<?php

namespace App\Observers;

use App\Enums\UserType;
use App\Models\Deal;
use App\Models\User;
use App\Services\NotificationService;

class DealObserver
{
    public function __construct(protected NotificationService $notificationService) {}

    /**
     * Handle the Deal "created" event.
     */
    public function created(Deal $deal): void
    {
        $creator = auth()->user();

        // 1. If created by Customer, notify all Admin role users
        if ($creator && $creator->type === UserType::CUSTOMER) {
            $admins = User::whereIn('type', [UserType::ADMIN, UserType::SUPER_ADMIN])->get();
            foreach ($admins as $admin) {
                $this->notificationService->info(
                    $admin->id,
                    'New Deal Created',
                    "A new deal \"{$deal->title}\" has been created by customer {$creator->name}.",
                    ['deal_id' => $deal->id, 'sound' => 'deal_created'],
                    "/admin/deals/{$deal->id}"
                );
            }
        }

        // 2. If assigned on creation, notify the assignee and Super Admins
        if ($deal->assigned_to) {
            $this->notifyAssigneeAndSuperAdmins($deal, 'New Deal Assigned');
        }
    }

    /**
     * Handle the Deal "updated" event.
     */
    public function updated(Deal $deal): void
    {
        $currentUser = auth()->user();
        if (! $currentUser) {
            return;
        }

        // 3. If assigned_to changed, notify the new assignee and Super Admins
        if ($deal->isDirty('assigned_to') && $deal->assigned_to) {
            $this->notifyAssigneeAndSuperAdmins($deal, 'Deal Assignment Updated');

            return; // Avoid double notification if other things changed
        }

        // 4. If admin role user changes anything, notify Super Admins
        if ($currentUser->type !== UserType::CUSTOMER && $deal->isDirty()) {
            $superAdmins = User::where('type', UserType::SUPER_ADMIN)->get();
            foreach ($superAdmins as $sa) {
                if ($sa->id !== $currentUser->id) { // Don't notify self
                    $this->notificationService->info(
                        $sa->id,
                        'Deal Updated',
                        "Admin {$currentUser->name} updated the deal \"{$deal->title}\".",
                        ['deal_id' => $deal->id],
                        "/admin/deals/{$deal->id}"
                    );
                }
            }
        }
    }

    /**
     * Notify the assignee and all Super Admins.
     */
    protected function notifyAssigneeAndSuperAdmins(Deal $deal, string $title): void
    {
        $currentUser = auth()->user();
        $recipients = User::where('type', UserType::SUPER_ADMIN)->get();

        // Add assignee if not already in recipients
        $assignee = User::find($deal->assigned_to);
        if ($assignee && ! $recipients->contains('id', $assignee->id)) {
            $recipients->push($assignee);
        }

        foreach ($recipients as $user) {
            if ($currentUser && $user->id === $currentUser->id) {
                continue; // Don't notify the person who made the change
            }

            $this->notificationService->info(
                $user->id,
                $title,
                "Deal \"{$deal->title}\" has been assigned to ".($assignee ? $assignee->name : 'unassigned').'.',
                ['deal_id' => $deal->id, 'sound' => 'assignment'],
                "/admin/deals/{$deal->id}"
            );
        }
    }
}
