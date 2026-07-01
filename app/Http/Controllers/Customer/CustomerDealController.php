<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerDealController extends Controller
{
    public function index()
    {
        $deals = Deal::where('dealable_type', 'App\Models\User')
            ->where('dealable_id', auth()->id())
            ->with(['assignedToUser', 'tasks', 'activities' => function ($q) {
                $q->where('type', '!=', 'note')->latest()->limit(5);
            }])
            ->latest()
            ->paginate(10);

        $stats = Deal::where('dealable_type', 'App\Models\User')
            ->where('dealable_id', auth()->id())
            ->selectRaw('
                count(*) as total_count,
                sum(value) as total_value,
                sum(case when stage in ("new", "proposal", "negotiation") then 1 else 0 end) as open_count,
                sum(case when stage in ("new", "proposal", "negotiation") then value else 0 end) as open_value,
                sum(case when stage = "won" then 1 else 0 end) as won_count,
                sum(case when stage = "won" then value else 0 end) as won_value,
                sum(case when stage = "lost" then 1 else 0 end) as lost_count,
                sum(case when stage = "lost" then value else 0 end) as lost_value
            ')
            ->first();

        return Inertia::render('Customer/Deals/Index', [
            'deals' => $deals,
            'stats' => $stats,
        ]);
    }

    public function show(Deal $deal)
    {
        if ($deal->dealable_type !== 'App\Models\User' || $deal->dealable_id !== auth()->id()) {
            abort(403);
        }

        $deal->load(['activities' => function ($q) {
            $q->where('type', '!=', 'note')->latest();
        }, 'assignedToUser', 'media', 'tasks' => function ($q) {
            $q->latest();
        }]);

        return Inertia::render('Customer/Deals/Show', [
            'deal' => $deal,
        ]);
    }

    public function create()
    {
        return Inertia::render('Customer/Deals/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'probability' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
            'expected_close_date' => 'nullable|date|after:today',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        $validated['probability'] ??= 50;

        $deal = new Deal($validated);
        $deal->stage = 'new';
        $deal->dealable_type = 'App\Models\User';
        $deal->dealable_id = auth()->id();
        $deal->created_by = auth()->id();
        $deal->save();

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $deal->addMedia($file)->toMediaCollection('attachments');
            }
        }

        return redirect()->route('customer.deals.index')->with('success', 'Deal created successfully.');
    }
}
