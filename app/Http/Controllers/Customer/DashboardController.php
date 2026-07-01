<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $dealsCount = \App\Models\Deal::where('dealable_type', 'App\Models\User')
            ->where('dealable_id', auth()->id())
            ->whereNotIn('stage', ['won', 'lost'])
            ->count();

        // Mock last activity for now until we have an activity log system for customers
        $lastActivity = 'Just now';

        return Inertia::render('Customer/Pages/Dashboard', [
            'dealsCount' => $dealsCount,
            'lastActivity' => $lastActivity,
        ]);
    }
}
