<?php

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('business_config')) {
    /**
     * Get the business setting value for the given key.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function business_config($key, $default = null)
    {
        $settings = Cache::remember('business_settings_all', 60 * 24, function () {
            return BusinessSetting::all()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}
