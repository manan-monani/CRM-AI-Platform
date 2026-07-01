<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(protected CustomerService $customerService)
    {
    }

    public function index(): Response
    {
        Gate::authorize(Permissions::CUSTOMER_MANAGEMENT);

        return inertia('Admin/Customers/Index', [
            'customers' => $this->customerService->getAll(request()->only('search')),
        ]);
    }

    public function show(User $customer): Response
    {
        Gate::authorize(Permissions::CUSTOMER_MANAGEMENT);

        $customer->load(['customerProfile', 'brand', 'activityLogs']);

        return inertia('Admin/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize(Permissions::CUSTOMER_MANAGEMENT);

        return inertia('Admin/Customers/Create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Gate::authorize(Permissions::CUSTOMER_MANAGEMENT);

        $this->customerService->create($request->validated());

        return to_route('admin.customers.index')->with('success', 'Customer created successfully.');
    }

    public function edit(User $customer): Response
    {
        Gate::authorize(Permissions::CUSTOMER_MANAGEMENT);

        $customer->load('customerProfile');

        return inertia('Admin/Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, User $customer): RedirectResponse
    {
        Gate::authorize(Permissions::CUSTOMER_MANAGEMENT);

        $validated = $request->validated();
        unset($validated['password_confirmation']);

        $this->customerService->update($customer, $validated, $request->file('profile_image'));

        return to_route('admin.customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(User $customer): RedirectResponse
    {
        Gate::authorize(Permissions::CUSTOMER_MANAGEMENT);

        $this->customerService->delete($customer);

        return back()->with('success', 'Customer deleted successfully.');
    }
}