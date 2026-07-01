<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    use \App\Traits\UploadsMedia, LogsActivity;

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return User::query()
            ->where('type', UserType::CUSTOMER)
            ->with(['customerProfile'])
            ->when(!empty($filters['search']), function ($query) use ($filters) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('customerProfile', function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            }
            );
        })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): User
    {
        $customer = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'type' => UserType::CUSTOMER,
        ]);

        // Create customer profile
        $customer->customerProfile()->create([
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
        ]);

        $this->logActivity('create_customer', "Created customer {$customer->name}");

        return $customer;
    }

    public function update(User $customer, array $data, $image = null): User
    {
        // Handle password
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        }
        else {
            unset($data['password']);
        }

        // Handle file upload
        if ($image) {
            if ($customer->profile_image) {
                $this->deleteOne($customer->profile_image);
            }
            $data['profile_image'] = $this->uploadOne($image, 'customers');
        }

        $customer->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ?? $customer->password,
            'profile_image' => $data['profile_image'] ?? $customer->profile_image,
        ]);

        // Update customer profile
        if ($customer->customerProfile) {
            $customer->customerProfile->update([
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
            ]);
        }

        $this->logActivity('update_customer', "Updated customer {$customer->name}");

        return $customer;
    }

    public function delete(User $customer): void
    {
        $this->logActivity('delete_customer', "Deleted customer {$customer->name}");
        $customer->delete();
    }

    public function createFromOAuth(array $oauthData): User
    {
        $customer = User::create([
            'name' => $oauthData['name'],
            'email' => $oauthData['email'],
            'google_id' => $oauthData['google_id'],
            'avatar' => $oauthData['avatar'] ?? null,
            'type' => UserType::CUSTOMER,
            'email_verified_at' => now(), // OAuth users are auto-verified
        ]);

        // Create customer profile
        $customer->customerProfile()->create();

        $this->logActivity('create_customer_oauth', "Created customer {$customer->name} via Google OAuth");

        return $customer;
    }
}