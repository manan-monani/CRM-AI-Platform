<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        // Customer Statistics
        $totalCustomers = User::where('type', UserType::CUSTOMER)->count();
        $newCustomers = User::where('type', UserType::CUSTOMER)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();
        $activeCustomers = User::where('type', UserType::CUSTOMER)
            ->whereHas('customerProfile')
            ->count();

        // Lead Statistics
        $totalLeads = Lead::count();
        $newLeads = Lead::where('created_at', '>=', Carbon::now()->subDays(30))->count();
        $activeLeads = Lead::whereIn('status', ['new', 'contacted', 'qualified'])->count();

        // Deals Statistics (Total counts across all statuses)
        $totalActiveDeals = Deal::visibleTo($currentUser)->count();

        // Task Statistics (Total counts across all statuses)
        $totalActiveTasks = Task::visibleTo($currentUser)->count();

        // Monitoring Logic (Detailed Feeds)
        $now = Carbon::now();
        $startOfToday = $now->copy()->startOfDay();
        $weekAway = $now->copy()->addDays(7)->endOfDay();

        // Fetch Tasks (Filtered for monitoring)
        $tasks = Task::visibleTo($currentUser)
            ->with(['assignedToUser', 'taskable'])
            ->get();

        // Fetch Deals (Filtered for monitoring)
        $deals = Deal::visibleTo($currentUser)
            ->with(['assignedToUser', 'dealable'])
            ->get();

        // Sorting Logic: Own items first, then by date
        $sortFn = function ($item) use ($currentUser) {
            $isOwn = $item->assigned_to === $currentUser->id ? 0 : 1;
            $date = $item->due_date ?? $item->expected_close_date;

            return [$isOwn, $date ? $date->timestamp : PHP_INT_MAX];
        };

        $overdueTasks = $tasks->filter(fn ($t) => $t->due_date && $t->due_date < $startOfToday)
            ->sortBy($sortFn)
            ->values();

        $upcomingTasks = $tasks->filter(fn ($t) => $t->due_date && $t->due_date >= $startOfToday && $t->due_date <= $weekAway)
            ->sortBy($sortFn)
            ->values();

        $overdueDeals = $deals->filter(fn ($d) => $d->expected_close_date && $d->expected_close_date < $startOfToday)
            ->sortBy($sortFn)
            ->values();

        $upcomingDeals = $deals->filter(fn ($d) => $d->expected_close_date && $d->expected_close_date >= $startOfToday && $d->expected_close_date <= $weekAway)
            ->sortBy($sortFn)
            ->values();

        // Staff Statistics (Internal only) - Super Admin Only
        $staffStats = [];
        if ($isSuperAdmin) {
            $staffStats = User::whereIn('type', [UserType::SUPER_ADMIN, UserType::ADMIN])
                ->get()
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'type' => $u->type,
                    'pending_tasks' => Task::where('assigned_to', $u->id)->whereIn('status', ['pending', 'in_progress'])->count(),
                    'completed_tasks' => Task::where('assigned_to', $u->id)->where('status', 'completed')->count(),
                    'due_tasks' => Task::where('assigned_to', $u->id)->whereIn('status', ['pending', 'in_progress'])->where('due_date', '<', $startOfToday)->count(),
                    'upcoming_tasks' => Task::where('assigned_to', $u->id)->whereIn('status', ['pending', 'in_progress'])->whereBetween('due_date', [$startOfToday, $weekAway])->count(),
                    'open_deals' => Deal::where('assigned_to', $u->id)->whereIn('status', ['open', 'active'])->count(),
                    'completed_deals' => Deal::where('assigned_to', $u->id)->where('status', 'won')->count(),
                    'due_deals' => Deal::where('assigned_to', $u->id)->whereIn('status', ['open', 'active'])->where('expected_close_date', '<', $startOfToday)->count(),
                    'upcoming_deals' => Deal::where('assigned_to', $u->id)->whereIn('status', ['open', 'active'])->whereBetween('expected_close_date', [$startOfToday, $weekAway])->count(),
                ]);
        }

        // Recent Records
        $recentCustomers = User::where('type', UserType::CUSTOMER)
            ->with('customerProfile')
            ->latest()
            ->limit(10)
            ->get();

        $recentLeads = Lead::latest()
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Pages/Dashboard', [
            'stats' => [
                'customers' => [
                    'total' => $totalCustomers,
                    'new' => $newCustomers,
                    'active' => $activeCustomers,
                ],
                'leads' => [
                    'total' => $totalLeads,
                    'new' => $newLeads,
                    'active' => $activeLeads,
                ],
                'deals' => [
                    'total' => $totalActiveDeals,
                ],
                'tasks' => [
                    'total' => $totalActiveTasks,
                ],
                'staff' => $staffStats,
            ],
            'monitoring' => [
                'tasks' => [
                    'overdue' => $overdueTasks,
                    'upcoming' => $upcomingTasks,
                ],
                'deals' => [
                    'overdue' => $overdueDeals,
                    'upcoming' => $upcomingDeals,
                ],
            ],
            'recentCustomers' => $recentCustomers,
            'recentLeads' => $recentLeads,
        ]);
    }
}
