<?php

namespace App\Services;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use \App\Traits\UploadsMedia, LogsActivity;

    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = User::query()->with('roles');

        // Filter to show only organization staff (exclude customers)
        $query->whereIn('type', [\App\Enums\UserType::ADMIN, \App\Enums\UserType::SUPER_ADMIN]);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'type' => $data['type'] ?? \App\Enums\UserType::ADMIN,
            'brand_id' => $data['brand_id'] ?? null,
        ]);

        if (in_array($user->type, [\App\Enums\UserType::ADMIN, \App\Enums\UserType::SUPER_ADMIN])) {
            $user->adminProfile()->create();
        }

        if ($user->type === \App\Enums\UserType::CUSTOMER) {
            $user->customerProfile()->create();
            $customerRole = \App\Models\Role::where('name', 'customer')->first();
            if ($customerRole) {
                $user->roles()->attach($customerRole);
            }
        }

        if (isset($data['roles'])) {
            $user->roles()->sync($data['roles']);
            $this->syncSalesRole($user);
        }

        $this->logActivity('create_user', "Created user {$user->name}");

        return $user;
    }

    public function update(User $user, array $data, $image = null): User
    {
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($image) {
            if ($user->profile_image) {
                $this->deleteOne($user->profile_image);
            }
            $data['profile_image'] = $this->uploadOne($image, 'profile');
        }

        $user->update(\Illuminate\Support\Arr::except($data, ['roles']));

        if (isset($data['roles']) && ! $user->isSuperAdmin()) {
            $user->roles()->sync($data['roles']);
            $this->syncSalesRole($user);
        }

        $this->logActivity('update_user', "Updated user {$user->name}");

        return $user;
    }

    public function delete(User $user): void
    {
        if ($user->isSuperAdmin()) {
            throw new \Exception('Super Admin users cannot be deleted.');
        }

        $this->logActivity('delete_user', "Deleted user {$user->name}");
        $user->delete();
    }

    public function updateStatus(User $user, string $status): void
    {
        $user->forceFill([
            'status' => $status,
        ])->save();

        $this->logActivity('update_user_status', "Updated user status for {$user->name} to {$status}");
    }

    protected function syncSalesRole(User $user): void
    {
        $role = $user->roles()->first();
        $salesRole = null;

        if ($role) {
            $roleSlug = \Illuminate\Support\Str::slug($role->name);
            if (in_array($roleSlug, ['manager', 'sales-manager'])) {
                $salesRole = \App\Enums\SalesRole::MANAGER;
            } elseif (in_array($roleSlug, ['rep', 'sales-rep', 'sales-representative'])) {
                $salesRole = \App\Enums\SalesRole::REP;
            } elseif (in_array($roleSlug, ['admin', 'sales-admin'])) {
                $salesRole = \App\Enums\SalesRole::ADMIN;
            }
        }

        if ($user->sales_role !== $salesRole) {
            $user->forceFill(['sales_role' => $salesRole])->save();
        }
    }
}
