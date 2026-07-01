<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\Lead;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LeadService
{
    use LogsActivity;

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return Lead::query()
            ->with(['convertedCustomer', 'leadSource'])
            ->when(!empty($filters['search']), function ($query) use ($filters) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('company', 'like', "%{$search}%");
        })
            ->when(!empty($filters['status']), function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        })
            ->when(!empty($filters['source']), function ($query) use ($filters) {
            $query->where('source', $filters['source']);
        })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Lead
    {
        $lead = Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'message' => $data['message'] ?? null,
            'source' => $data['source'] ?? 'landing_page',
            'status' => 'new',
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
        ]);

        $this->logActivity('create_lead', "Created lead {$lead->name}");

        return $lead;
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'message' => $data['message'] ?? null,
            'source' => $data['source'] ?? $lead->source,
        ]);

        $this->logActivity('update_lead', "Updated lead {$lead->name}");

        return $lead;
    }

    public function updateStatus(Lead $lead, string $status): void
    {
        $lead->forceFill([
            'status' => $status,
        ])->save();

        $this->logActivity('update_lead_status', "Updated lead status for {$lead->name} to {$status}");
    }

    public function convertToCustomer(Lead $lead, array $additionalData = []): User
    {
        if ($lead->isConverted()) {
            throw new \Exception('Lead has already been converted to customer.');
        }

        // Check if email already exists in users table
        $emailToUse = $additionalData['email'] ?? $lead->email;
        $existingUser = User::where('email', $emailToUse)->first();

        if ($existingUser) {
            // If user exists and is a customer, link the lead to them
            if ($existingUser->type === UserType::CUSTOMER) {
                return DB::transaction(function () use ($lead, $existingUser) {
                    // Update lead status to point to existing customer
                    $lead->forceFill([
                        'status' => 'converted',
                        'converted_to_customer_id' => $existingUser->id,
                        'converted_at' => now(),
                    ])->save();

                    $this->logActivity('convert_lead', "Linked lead {$lead->name} to existing customer");

                    return $existingUser;
                });
            }

            // If user exists but is not a customer (e.g., admin), throw error
            throw new \Exception("A user with email {$emailToUse} already exists but is not a customer. Please use a different email or contact the user.");
        }

        return DB::transaction(function () use ($lead, $additionalData) {
            // Create customer user
            $customer = User::create([
                'name' => $additionalData['name'] ?? $lead->name,
                'email' => $additionalData['email'] ?? $lead->email,
                'password' => isset($additionalData['password'])
                ?Hash::make($additionalData['password'])
                : Hash::make(\Illuminate\Support\Str::random(16)),
                'type' => UserType::CUSTOMER,
                'brand_id' => $additionalData['brand_id'] ?? null,
            ]);

            // Create customer profile
            $customer->customerProfile()->create([
                'phone' => $additionalData['phone'] ?? $lead->phone,
                'address' => $additionalData['address'] ?? null,
                'city' => $additionalData['city'] ?? null,
            ]);

            // Update lead status
            $lead->forceFill([
                'status' => 'converted',
                'converted_to_customer_id' => $customer->id,
                'converted_at' => now(),
            ])->save();

            $this->logActivity('convert_lead', "Converted lead {$lead->name} to customer");

            return $customer;
        });
    }

    public function delete(Lead $lead): void
    {
        $this->logActivity('delete_lead', "Deleted lead {$lead->name}");
        $lead->delete();
    }
}