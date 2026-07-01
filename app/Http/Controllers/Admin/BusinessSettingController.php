<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BusinessSettingController extends Controller
{
    public function __construct(protected \App\Services\BusinessSettingService $businessSettingService)
    {
    }

    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::BUSINESS_BRANDING);

        return inertia('Admin/Business/Branding', [
            'settings' => $this->businessSettingService->getSettings([], true),
            'default_colors' => config('branding.colors'),
        ]);
    }

    public function update(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::BUSINESS_BRANDING);

        $data = $request->except(['_token', '_method', 'logo_url', 'favicon_url', 'cover_url', 'logo_dark_url', 'favicon_dark_url']);
        $files = [
            'logo_url' => $request->file('logo_url'),
            'favicon_url' => $request->file('favicon_url'),
            'cover_url' => $request->file('cover_url'),
            'logo_dark_url' => $request->file('logo_dark_url'),
            'favicon_dark_url' => $request->file('favicon_dark_url'),
        ];

        $this->businessSettingService->updateBranding($data, $files);

        return back()->with('success', 'Settings updated successfully.');
    }

    public function pageBuilder()
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::LEAD_GENERATION_PAGE);

        return inertia('Admin/Business/PageBuilder', [
            'settings' => $this->businessSettingService->getSettings([], true),
            'default_form' => \App\Models\LeadForm::where('is_default', true)->with('fields')->first(),
        ]);
    }

    public function updatePageBuilder(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::LEAD_GENERATION_PAGE);

        $data = $request->except(['_token', '_method', 'landing_hero_image', 'landing_feature_1_icon', 'landing_feature_2_icon', 'landing_feature_3_icon', 'landing_feature_4_icon']);

        $files = [
            'landing_hero_image' => $request->file('landing_hero_image'),
            'landing_feature_1_icon' => $request->file('landing_feature_1_icon'),
            'landing_feature_2_icon' => $request->file('landing_feature_2_icon'),
            'landing_feature_3_icon' => $request->file('landing_feature_3_icon'),
            'landing_feature_4_icon' => $request->file('landing_feature_4_icon'),
        ];

        $this->businessSettingService->updateBranding($data, $files);

        return back()->with('success', 'Lead Generation Page updated successfully.');
    }

    public function homePageBuilder()
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::HOME_PAGE_BUILDER);

        return inertia('Admin/Business/HomePageBuilder', [
            'settings' => $this->businessSettingService->getSettings([], true),
        ]);
    }

    public function updateHomePageBuilder(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::HOME_PAGE_BUILDER);

        $data = $request->except(['_token', '_method']);
        // Files processing can be added here if we allow uploading images directly within sections
        // For now, we assume images are handled via media library or existing settings

        $this->businessSettingService->updateSettings($data);

        return back()->with('success', 'Home Page updated successfully.');
    }
}