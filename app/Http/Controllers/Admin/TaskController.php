<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaskRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService)
    {
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission(Permissions::TASK_MANAGEMENT), 403);

        $user = auth()->user();
        $tasks = Task::with(['assignedToUser', 'taskable', 'createdByUser'])
            ->visibleTo($user)
            ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                }
                );
            })
            ->when($request->status !== 'all', function ($query) use ($request) {
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }
        })
            ->when($request->user_id, function ($query, $userId) {
            $query->where('assigned_to', $userId);
        })
            ->when($request->deal_id, function ($query, $dealId) {
            $query->where('taskable_type', \App\Models\Deal::class)
                ->where('taskable_id', $dealId);
        })
            ->orderBy('due_date', 'asc')
            ->paginate(10);

        $dealsQuery = \App\Models\Deal::query();
        if (!$user->isSuperAdmin()) {
            $dealsQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhereNull('assigned_to');
            });
        }

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['status', 'user_id', 'deal_id', 'search']),
            'users' => $user->isSuperAdmin() ?User::where('type', '!=', UserType::CUSTOMER)->select('id', 'name')->get() : [],
            'deals' => $dealsQuery->with('assignedToUser:id,name')->select('id', 'title', 'assigned_to')->get(),
        ]);
    }

    private function authorizeTaskAccess($user, Task $task)
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        $hasAccess = $task->assigned_to === $user->id ||
            $task->created_by === $user->id ||
            $task->assigned_to === null ||
            ($task->taskable_type === \App\Models\Deal::class && $task->taskable && $task->taskable->assigned_to === $user->id);

        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this task.');
        }
    }

    public function show(Task $task)
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(Permissions::TASK_MANAGEMENT), 403);
        $this->authorizeTaskAccess($user, $task);

        $task->load(['assignedToUser', 'createdByUser', 'taskable.assignedToUser', 'activities.user', 'media']);

        return Inertia::render('Admin/Tasks/Show', [
            'task' => $task,
            'users' => User::where('type', '!=', UserType::CUSTOMER)->select('id', 'name')->get(),
        ]);
    }

    public function store(StoreTaskRequest $request)
    {
        abort_unless(auth()->user()->hasPermission(Permissions::TASK_MANAGEMENT), 403);

        $taskable = null;
        if ($request->taskable_type && $request->taskable_id) {
            $taskable = $request->taskable_type::find($request->taskable_id);
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $this->taskService->createTask($request->validated(), $user, $taskable);

        return back()->with('success', 'Task created successfully.');
    }

    public function complete(Task $task)
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(Permissions::TASK_MANAGEMENT), 403);
        $this->authorizeTaskAccess($user, $task);

        $this->taskService->completeTask($task);

        return back()->with('success', 'Task completed.');
    }

    public function update(Request $request, Task $task)
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(Permissions::TASK_MANAGEMENT), 403);
        $this->authorizeTaskAccess($user, $task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'due_date' => 'sometimes|nullable|date',
            'status' => 'sometimes|required|string|in:pending,in_progress,completed,cancelled',
            'priority' => 'sometimes|nullable|string|in:low,medium,high',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'attachments' => 'sometimes|nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ]);

        $this->taskService->updateTask($task, $validated);

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(Permissions::TASK_MANAGEMENT), 403);
        $this->authorizeTaskAccess($user, $task);

        $task->delete();

        return back()->with('success', 'Task deleted.');
    }
}