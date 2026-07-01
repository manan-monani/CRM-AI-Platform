<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadFormSubmissionRequest;
use App\Models\Lead;
use App\Models\LeadForm;
use App\Models\LeadFormSubmission;
use Inertia\Response;

class LeadGenerationController extends Controller
{
    public function index(): Response
    {
        $query = LeadForm::where('status', 'active');

        if (request()->has('form_id')) {
            $query->where('id', request('form_id'));
        } else {
            $query->where('is_default', true);
        }

        $leadForm = $query->first();

        // If specific form requested but not found, fallback to default
        if (!$leadForm && request()->has('form_id')) {
            $leadForm = LeadForm::where('is_default', true)
                ->where('status', 'active')
                ->first();
        }

        return inertia('Guest/LeadGeneration', [
            'leadForm' => $leadForm?->load('fields'),
        ]);
    }

    public function storeLead(LeadFormSubmissionRequest $request)
    {
        $validated = $request->validated();

        $formId = $request->input('form_id');
        $form = LeadForm::findOrFail($formId);

        // Calculate score based on form configuration if applicable
        // For now, simple submission logic

        $submission = LeadFormSubmission::create([
            'lead_form_id' => $form->id,
            'data' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Create Lead from submission if configured
        // Mapping fields would be complex here without strict schema,
        // so we'll just create a basic Lead for now or let an Observer handle it.
        // For this boilerplate, let's create a Lead directly.

        $lead = Lead::create([
            'first_name' => $validated['first_name'] ?? 'Unknown',
            'last_name' => $validated['last_name'] ?? '',
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'source' => 'Web Form: '.$form->name,
            'status' => 'new',
        ]);

        return back()->with('success', 'Thank you! We have received your details and will contact you shortly.');
    }
}