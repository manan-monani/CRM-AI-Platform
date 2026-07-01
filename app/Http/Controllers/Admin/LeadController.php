<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLeadRequest;
use App\Http\Requests\Admin\UpdateLeadRequest;
use App\Http\Requests\Admin\UpdateLeadStatusRequest;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class LeadController extends Controller
{
    public function __construct(protected LeadService $leadService)
    {
    }

    public function index(): Response
    {
        Gate::authorize(Permissions::LEAD_MANAGEMENT);

        return inertia('Admin/Leads/Index', [
            'leads' => $this->leadService->getAll(request()->only(['search', 'status', 'source'])),
            'leadSources' => LeadSource::where('is_active', true)->get(),
        ]);
    }

    public function show(Lead $lead): Response
    {
        Gate::authorize(Permissions::LEAD_MANAGEMENT);

        $lead->load(['convertedCustomer', 'leadSource']);

        return inertia('Admin/Leads/Show', [
            'lead' => $lead,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize(Permissions::LEAD_MANAGEMENT);

        return inertia('Admin/Leads/Create', [
            'leadSources' => LeadSource::where('is_active', true)->get(),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        Gate::authorize(Permissions::LEAD_MANAGEMENT);

        $this->leadService->create($request->validated());

        return to_route('admin.leads.index')->with('success', 'Lead created successfully.');
    }

    public function edit(Lead $lead): Response
    {
        Gate::authorize(Permissions::LEAD_MANAGEMENT);

        return inertia('Admin/Leads/Edit', [
            'lead' => $lead,
            'leadSources' => LeadSource::where('is_active', true)->get(),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        Gate::authorize(Permissions::LEAD_MANAGEMENT);

        $this->leadService->update($lead, $request->validated());

        return to_route('admin.leads.index')->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        Gate::authorize(Permissions::LEAD_MANAGEMENT);

        if ($lead->isConverted()) {
            return back()->with('error', 'Cannot delete a converted lead.');
        }

        $this->leadService->delete($lead);

        return back()->with('success', 'Lead deleted successfully.');
    }

    public function updateStatus(UpdateLeadStatusRequest $request, Lead $lead): RedirectResponse
    {
        Gate::authorize(Permissions::LEAD_MANAGEMENT);

        $this->leadService->updateStatus($lead, $request->validated()['status']);

        return back()->with('success', 'Lead status updated successfully.');
    }

    public function convertToCustomer(Lead $lead): RedirectResponse
    {
        Gate::authorize(Permissions::LEAD_MANAGEMENT);

        try {
            $customer = $this->leadService->convertToCustomer($lead);

            return to_route('admin.customers.show', $customer)->with('success', 'Lead converted to customer successfully.');
        }
        catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}