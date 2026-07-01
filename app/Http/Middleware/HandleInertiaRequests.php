<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $permissions = [];

        if ($user) {
            if ($user->isSuperAdmin()) {
                $permissions = ['*'];
            } else {
                // Direct query to bypass Eloquent relationship loading issues
                $permissions = \Illuminate\Support\Facades\DB::table('user_roles')
                    ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
                    ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                    ->where('user_roles.user_id', $user->id)
                    ->distinct()
                    ->pluck('permissions.name')
                    ->all();
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'is_super_admin' => $user?->isSuperAdmin() ?? false,
                'permissions' => $permissions,
                'available_permissions' => collect(\App\Constants\Permissions::getAll())->map(function ($section) {
                    if (isset($section['sub_modules'])) {
                        $section['sub_modules'] = collect($section['sub_modules'])->map(function ($module) {
                            if (isset($module['route'])) {
                                try {
                                    $module['url'] = route($module['route']);
                                } catch (\Exception $e) {
                                    $module['url'] = '#'; // Fallback to avoid crash
                                }
                            }

                            return $module;
                        })->toArray();
                    }

                    return $section;
                })->toArray(),
            ],
            'branding' => array_merge(config('branding.theme'), [
                'business_settings' => \App\Models\BusinessSetting::all()->pluck('value', 'key')->toArray(),
            ]),
            'locale' => app()->getLocale(),
            'translations' => file_exists(lang_path(app()->getLocale().'.json'))
                ? json_decode(file_get_contents(lang_path(app()->getLocale().'.json')), true)
                : [],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'reminder_counts' => function () use ($user) {
                if (! $user) {
                    return ['pending' => 0, 'overdue' => 0];
                }

                $query = \App\Models\Reminder::query()->where('status', 'pending');

                if (! $user->isSuperAdmin()) {
                    $query->where('assigned_to', $user->id);
                }

                return [
                    'pending' => (clone $query)->count(),
                    'overdue' => $query->where('remind_at', '<', now())->count(),
                ];
            },
            'task_counts' => function () use ($user) {
                if (! $user) {
                    return ['pending' => 0, 'overdue' => 0];
                }

                $query = \App\Models\Task::visibleTo($user);

                return [
                    'pending' => (clone $query)->count(),
                    'overdue' => $query->where('due_date', '<', now())->count(),
                ];
            },
            'lead_counts' => function () {
                return [
                    'new' => \App\Models\Lead::count(),
                ];
            },
            'deal_counts' => function () use ($user) {
                if (! $user) {
                    return ['open' => 0];
                }

                $query = \App\Models\Deal::visibleTo($user);

                return [
                    'open' => $query->count(),
                ];
            },
            'customer_counts' => function () {
                return [
                    'total' => \App\Models\User::where('type', \App\Enums\UserType::CUSTOMER)->count(),
                ];
            },
            'app_mode' => config('app.mode', env('APP_MODE', 'production')),
        ];
    }
}