<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(): \Inertia\Response
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::MEMBER_DIRECTORY);

        return inertia('Admin/Members/Index', [
            'users' => $this->userService->getAll(request()->only('search')),
            'roles' => Role::all(),
        ]);
    }

    public function create(): \Inertia\Response
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::MEMBER_DIRECTORY);

        return inertia('Admin/Members/Create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(StoreUserRequest $request): \Illuminate\Http\RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::MEMBER_DIRECTORY);

        $data = $request->validated();
        if (isset($data['role_id'])) {
            $data['roles'] = [$data['role_id']];
            unset($data['role_id']);
        }

        $this->userService->create($data);

        return to_route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): \Inertia\Response
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::MEMBER_DIRECTORY);

        $user->load('roles');
        $userData = $user->toArray();
        $userData['role_id'] = $user->roles->first()?->id ?? '';

        return inertia('Admin/Members/Edit', [
            'user' => $userData,
            'roles' => Role::all(),
        ]);
    }

    public function update(\App\Http\Requests\Admin\UpdateUserRequest $request, User $user): \Illuminate\Http\RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::MEMBER_DIRECTORY);

        $validated = $request->validated();

        if ($user->isSuperAdmin()) {
            $newRoleId = $validated['role_id'] ?? ($validated['roles'][0] ?? null);
            $currentRoleId = $user->roles->first()?->id;

            if ($newRoleId && $newRoleId != $currentRoleId) {
                return back()->with('error', 'Super Admin roles cannot be changed.');
            }
        }

        if (isset($validated['role_id'])) {
            $validated['roles'] = [$validated['role_id']];
            unset($validated['role_id']);
        }

        // Remove password_confirmation
        unset($validated['password_confirmation']);

        $this->userService->update($user, $validated);

        return to_route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function updateStatus(\App\Http\Requests\Admin\UpdateUserStatusRequest $request, User $user): \Illuminate\Http\RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::MEMBER_DIRECTORY);

        $this->userService->updateStatus($user, $request->validated()['status']);

        return back()->with('success', 'User status updated successfully.');
    }

    public function destroy(User $user): \Illuminate\Http\RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::MEMBER_DIRECTORY);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super Admin users cannot be deleted.');
        }

        $this->userService->delete($user);

        return back()->with('success', 'User deleted successfully.');
    }
}
