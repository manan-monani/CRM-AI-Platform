<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreActivityRequest;
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Models\Activity;
use App\Services\ActivityService;

class ActivityController extends Controller
{
    public function __construct(protected ActivityService $activityService)
    {
    }

    public function index(\Illuminate\Http\Request $request)
    {
        abort_unless(auth()->user()->hasPermission(Permissions::ACTIVITY_MANAGEMENT), 403);

        $activities = Activity::with(['activityable', 'performedByUser'])
            ->visibleTo(auth()->user())
            ->when($request->search, function ($query, $search) {
            $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('outcome', 'like', "%{$search}%")
                        ->orWhereHasMorph(
                        'activityable',
                    ['App\Models\User', 'App\Models\Deal'],
                        function ($q, $type) use ($search) {
                    if ($type === 'App\Models\User') {
                        $q->where('name', 'like', "%{$search}%");
                    }
                    elseif ($type === 'App\Models\Deal') {
                        $q->where('title', 'like', "%{$search}%");
                    }
                }
                );
            }
            );
        })
            ->when($request->type, function ($query, $type) {
            $query->where('type', $type);
        })
            ->when($request->user_id, function ($query, $userId) {
            $query->where('performed_by', $userId);
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $usersQuery = \App\Models\User::select('id', 'name', 'profile_image');
        if (!auth()->user()->isSuperAdmin()) {
            // If not super admin, we don't want to show the user filter at all
            // So we'll pass an empty list of users
            $usersQuery->whereRaw('1=0');
        }

        // Get distinct activity types from DB to populate filter dynamically
        $dbTypes = Activity::distinct()->pluck('type')->filter()->values()->toArray();
        $defaultTypes = ['call', 'meeting', 'email', 'task', 'note'];
        $types = array_unique(array_merge($defaultTypes, $dbTypes));

        return \Inertia\Inertia::render('Admin/Activities/Index', [
            'activities' => $activities,
            'filters' => $request->only(['search', 'type', 'user_id']),
            'users' => $usersQuery->get(),
            'types' => array_values($types),
        ]);
    }

    public function store(StoreActivityRequest $request)
    {
        abort_unless(auth()->user()->hasPermission(Permissions::ACTIVITY_MANAGEMENT), 403);

        $activityableType = $request->activityable_type;
        $activityableId = $request->activityable_id;
        $activityable = $activityableType::findOrFail($activityableId);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $this->activityService->logActivity($request->validated(), $activityable, $user);

        return back()->with('success', 'Activity logged successfully.');
    }

    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        abort_unless(auth()->user()->hasPermission(Permissions::ACTIVITY_MANAGEMENT), 403);

        $user = auth()->user();

        // Check if user is allowed to edit this activity (e.g. only their own if they are a sales rep)
        if ($user->isSalesRep() && $activity->performed_by !== $user->id) {
            abort(403, 'Unauthorized to update this activity.');
        }

        $activity->update($request->validated());

        return back()->with('success', 'Activity updated successfully.');
    }

    public function destroy(Activity $activity)
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(Permissions::ACTIVITY_MANAGEMENT), 403);

        if ($user->isSalesRep() && $activity->performed_by !== $user->id) {
            abort(403, 'Unauthorized to delete this activity.');
        }

        $activity->delete();

        return back()->with('success', 'Activity deleted successfully.');
    }
}