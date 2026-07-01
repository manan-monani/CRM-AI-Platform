<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDealRequest;
use App\Http\Requests\Admin\UpdateDealRequest;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\DealUpdatedNotification;
use App\Services\DealService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class DealController extends Controller
{
    public function __construct(protected DealService $dealService)
    {
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission(Permissions::DEAL_MANAGEMENT), 403);

        $user = auth()->user();
        $deals = Deal::with(['dealable', 'assignedToUser'])
            ->visibleTo($user)
            ->when($request->search, function ($query, $search) {
            $query->where('title', 'like', "%{$search}%");
        })
            ->when($request->stage, function ($query, $stage) {
            $query->where('stage', $stage);
        })
            ->when($request->user_id, function ($query, $userId) {
            $query->where('assigned_to', $userId);
        })
            ->latest()
            ->paginate($request->input('view', 'board') === 'list' ? 10 : 1000)
            ->withQueryString();

        return Inertia::render('Admin/Deals/Index', [
            'deals' => $deals,
            'users' => $user->isSuperAdmin() ?User::whereIn('sales_role', ['admin', 'manager', 'rep'])->select('id', 'name')->get() : [],
            'filters' => $request->only(['search', 'stage', 'user_id', 'view']),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()->hasPermission(Permissions::DEAL_MANAGEMENT), 403);

        $preselected = null;
        if ($request->has('customer_id')) {
            $preselected = [
                'dealable_type' => User::class ,
                'dealable_id' => (int)$request->customer_id,
            ];
        }
        elseif ($request->has('lead_id')) {
            $preselected = [
                'dealable_type' => Lead::class ,
                'dealable_id' => (int)$request->lead_id,
            ];
        }

        return Inertia::render('Admin/Deals/Create', [
            'leads' => Lead::select('id', 'name')->get()->map(function ($lead) {
            return ['id' => $lead->id, 'name' => $lead->name, 'type' => Lead::class];
        }),
            'customers' => User::where('type', UserType::CUSTOMER)->select('id', 'name')->get()->map(function ($user) {
            return ['id' => $user->id, 'name' => $user->name, 'type' => User::class];
        }),
            'users' => User::whereIn('sales_role', ['admin', 'manager', 'rep'])->select('id', 'name')->get(),
            'preselected' => $preselected,
        ]);
    }

    public function store(StoreDealRequest $request)
    {
        abort_unless(auth()->user()->hasPermission(Permissions::DEAL_MANAGEMENT), 403);

        $dealableType = $request->dealable_type;
        $dealableId = $request->dealable_id;
        $dealable = $dealableType::findOrFail($dealableId);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $this->dealService->createDeal($request->validated(), $dealable, $user);

        return redirect()->route('admin.deals.index')->with('success', 'Deal created successfully.');
    }

    public function show(Deal $deal)
    {
        $user = auth()->user();
        if ($user->isSalesRep() && $deal->assigned_to !== $user->id) {
            abort(403, 'Unauthorized access to this deal.');
        }

        $deal->load(['dealable', 'assignedToUser', 'activities.user', 'media', 'activities' => function ($q) {
            $q->latest();
        }, 'tasks.assignedToUser', 'tasks.media', 'tasks' => function ($q) {
            $q->orderBy('due_date', 'asc');
        }]);

        if ($deal->dealable instanceof \App\Models\User) {
            $deal->dealable->load('lead');
        }

        return Inertia::render('Admin/Deals/Show', [
            'deal' => $deal,
            'users' => User::select('id', 'name', 'profile_image')->get(),
        ]);
    }

    public function edit(Deal $deal)
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(Permissions::DEAL_MANAGEMENT), 403);

        if ($user->isSalesRep() && $deal->assigned_to !== $user->id) {
            abort(403, 'Unauthorized access to this deal.');
        }

        $deal->load('media');

        return Inertia::render('Admin/Deals/Edit', [
            'deal' => $deal,
            'leads' => Lead::select('id', 'name')->get()->map(function ($lead) {
            return ['id' => $lead->id, 'name' => $lead->name, 'type' => Lead::class];
        }),
            'customers' => User::where('type', UserType::CUSTOMER)->select('id', 'name')->get()->map(function ($user) {
            return ['id' => $user->id, 'name' => $user->name, 'type' => User::class];
        }),
            'users' => User::whereIn('sales_role', ['admin', 'manager', 'rep'])->select('id', 'name', 'profile_image')->get(),
        ]);
    }

    public function update(UpdateDealRequest $request, Deal $deal)
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(Permissions::DEAL_MANAGEMENT), 403);

        if ($user->isSalesRep() && $deal->assigned_to !== $user->id) {
            abort(403, 'Unauthorized access to this deal.');
        }

        \Illuminate\Support\Facades\Log::info('Updating deal ' . $deal->id, $request->validated());
        $this->dealService->updateDeal($deal, $request->validated());

        // Notify Super Admins
        $superAdmins = User::where('type', UserType::SUPER_ADMIN)->get();
        Notification::send($superAdmins, new DealUpdatedNotification($deal, 'Updated'));

        return redirect()->route('admin.deals.show', $deal)->with('success', 'Deal updated successfully.');
    }

    public function updateStage(Request $request, Deal $deal)
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(Permissions::DEAL_MANAGEMENT), 403);

        if ($user->isSalesRep() && $deal->assigned_to !== $user->id) {
            abort(403, 'Unauthorized access to this deal.');
        }

        $request->validate(['stage' => 'required|string']);
        $this->dealService->updateStage($deal, $request->stage);

        // Notify Super Admins
        $superAdmins = User::where('type', UserType::SUPER_ADMIN)->get();
        Notification::send($superAdmins, new DealUpdatedNotification($deal, 'Stage Changed'));

        return back()->with('success', __('Deal stage updated successfully.'));
    }

    public function destroy(Deal $deal)
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(Permissions::DEAL_MANAGEMENT), 403);

        if ($user->isSalesRep() && $deal->assigned_to !== $user->id) {
            abort(403, 'Unauthorized access to this deal.');
        }

        $deal->delete();

        return redirect()->route('admin.deals.index')->with('success', 'Deal deleted successfully.');
    }
}