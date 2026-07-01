<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadForm;
use Illuminate\Http\Request;
use App\Services\LeadService;
use Inertia\Inertia;

class PublicLeadFormController extends Controller
{
    public function __construct(protected LeadService $leadService)
    {
    }

    public function show($slug)
    {
        $form = LeadForm::where('slug', $slug)
            ->where('status', 'active')
            ->with(['fields' => function ($query) {
            $query->orderBy('order_index');
        }])
            ->firstOrFail();

        // Get branding settings for logo and business name
        $brandingSettings = [];
        $landingSettings = [];

        $settingKeys = [
            'business_name', 'logo', 'primary',
            'landing_hero_image', 'landing_title', 'landing_subtitle',
        ];

        foreach ($settingKeys as $key) {
            $setting = \App\Models\BusinessSetting::where('key', $key)->first();
            if ($setting) {
                if (in_array($key, ['business_name', 'logo', 'primary'])) {
                    $brandingSettings[$key] = $setting->value;
                }
                else {
                    $landingSettings[$key] = $setting->value;
                }
            }
        }

        return Inertia::render('Public/LeadForm', [
            'form' => $form->toArray(),
            'branding' => $brandingSettings,
            'landing' => $landingSettings,
        ]);
    }

    public function store(Request $request, $slug)
    {
        $form = LeadForm::where('slug', $slug)
            ->where('status', 'active')
            ->with('fields')
            ->firstOrFail();

        // Dynamic validation rules based on fields
        $rules = [];
        foreach ($form->fields as $field) {
            if ($field->is_required) {
                $rules[$field->name] = 'required';
            }
            else {
                $rules[$field->name] = 'nullable';
            }

            if ($field->type === 'email') {
                $rules[$field->name] .= '|email';
            }
        }

        $validatedData = $request->validate($rules);

        // Extract standard fields vs custom fields
        $standardFields = ['name', 'email', 'phone', 'company', 'message'];
        $customData = [];
        $leadData = [
            'lead_form_id' => $form->id,
            'status' => 'new',
            'source' => $request->input('utm_source') ?: 'landing_page',
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        // Map common slugs to standard fields
        $slugToStandardMapping = [
            'full-name' => 'name',
            'name' => 'name',
            'email-address' => 'email',
            'email' => 'email',
            'phone-number' => 'phone',
            'phone' => 'phone',
            'company-name' => 'company',
            'company' => 'company',
            'message' => 'message',
            'your-message' => 'message',
        ];

        foreach ($validatedData as $key => $value) {
            if (isset($slugToStandardMapping[$key])) {
                $leadData[$slugToStandardMapping[$key]] = $value;
            }
            elseif (in_array($key, $standardFields)) {
                $leadData[$key] = $value;
            }
            else {
                $customData[$key] = $value;
            }
        }

        // Fallback for standard fields if mapped differently or missing
        // For MVP, we assume the form builder uses standard names for these if they exist.
        // If not, we might need a mapping strategy.
        // For now, let's ensure required standard columns are present.
        if (!isset($leadData['name'])) {
            $leadData['name'] = 'Lead #' . time();
        }
        if (!isset($leadData['email'])) {
            $leadData['email'] = 'no-email-' . time() . '@example.com';
        }

        $leadData['custom_data'] = $customData;

        $lead = Lead::create($leadData);

        // Notifications would go here

        return back()->with('success', 'Thank you! Your submission has been received.');
    }
}