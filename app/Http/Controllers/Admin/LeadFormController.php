<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Models\LeadForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class LeadFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize(Permissions::LEAD_GENERATION_FORM_MANAGEMENT);

        $forms = LeadForm::query()
            ->withCount('leads')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/LeadForms/Index', [
            'forms' => $forms,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize(Permissions::LEAD_GENERATION_FORM_MANAGEMENT);

        return Inertia::render('Admin/LeadForms/Builder', [
            'form' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize(Permissions::LEAD_GENERATION_FORM_MANAGEMENT);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'branding_settings' => 'nullable|array',
            'notification_emails' => 'nullable|array',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string',
            'fields.*.type' => 'required|string',
            'fields.*.placeholder' => 'nullable|string',
            'fields.*.is_required' => 'boolean',
            'fields.*.options' => 'nullable|array',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            LeadForm::where('is_default', true)->update(['is_default' => false]);
        }

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $count = 1;
        while (LeadForm::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $form = LeadForm::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'branding_settings' => $validated['branding_settings'] ?? null,
            'notification_emails' => $validated['notification_emails'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        foreach ($validated['fields'] as $index => $fieldData) {
            $isSystem = in_array($fieldData['label'], ['Full Name', 'Email Address']);
            $form->fields()->create([
                'label' => $fieldData['label'],
                'name' => Str::slug($fieldData['label']),
                'type' => $fieldData['type'],
                'placeholder' => $fieldData['placeholder'] ?? null,
                'is_required' => $isSystem ? true : ($fieldData['is_required'] ?? false),
                'options' => $fieldData['options'] ?? null,
                'order_index' => $index,
            ]);
        }

        return redirect()->route('admin.lead-generation-form.index')->with('success', 'Lead Form created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeadForm $lead_form)
    {
        Gate::authorize(Permissions::LEAD_GENERATION_FORM_MANAGEMENT);

        $lead_form->load('fields');

        return Inertia::render('Admin/LeadForms/Builder', [
            'form' => $lead_form,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeadForm $lead_form)
    {
        Gate::authorize(Permissions::LEAD_GENERATION_FORM_MANAGEMENT);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'branding_settings' => 'nullable|array',
            'notification_emails' => 'nullable|array',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string',
            'fields.*.type' => 'required|string',
            'fields.*.placeholder' => 'nullable|string',
            'fields.*.is_required' => 'boolean',
            'fields.*.options' => 'nullable|array',
            'is_default' => 'boolean',
        ]);

        if ($lead_form->is_default && $validated['status'] === 'inactive') {
            return back()->with('error', 'The default form must remain active.');
        }

        if ($validated['is_default'] ?? false) {
            LeadForm::where('id', '!=', $lead_form->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $lead_form->update([
            'name' => $validated['name'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'branding_settings' => $validated['branding_settings'],
            'notification_emails' => $validated['notification_emails'],
            'is_default' => $validated['is_default'] ?? false,
        ]);

        // Sync fields
        $lead_form->fields()->delete();

        foreach ($validated['fields'] as $index => $fieldData) {
            $isSystem = in_array($fieldData['label'], ['Full Name', 'Email Address']);
            $lead_form->fields()->create([
                'label' => $fieldData['label'],
                'name' => Str::slug($fieldData['label']),
                'type' => $fieldData['type'],
                'placeholder' => $fieldData['placeholder'] ?? null,
                'is_required' => $isSystem ? true : ($fieldData['is_required'] ?? false),
                'options' => $fieldData['options'] ?? null,
                'order_index' => $index,
            ]);
        }

        return redirect()->route('admin.lead-generation-form.index')->with('success', 'Lead Form updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeadForm $lead_form)
    {
        Gate::authorize(Permissions::LEAD_GENERATION_FORM_MANAGEMENT);

        if ($lead_form->is_default) {
            return back()->with('error', 'The default form cannot be deleted. Please set another form as default first.');
        }

        $lead_form->delete();

        return redirect()->route('admin.lead-generation-form.index')->with('success', 'Lead Form deleted successfully.');
    }

    /**
     * Set the specified lead form as default.
     */
    public function setDefault(LeadForm $lead_form)
    {
        Gate::authorize(Permissions::LEAD_GENERATION_FORM_MANAGEMENT);

        if ($lead_form->status !== 'active') {
            return back()->with('error', 'Only active forms can be set as default.');
        }

        LeadForm::where('is_default', true)->update(['is_default' => false]);

        $lead_form->update(['is_default' => true]);

        return back()->with('success', 'Lead Form set as default successfully.');
    }
}