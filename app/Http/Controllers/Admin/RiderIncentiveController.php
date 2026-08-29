<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiderIncentiveRule;
use App\Models\Setting;
use Illuminate\Http\Request;

class RiderIncentiveController extends Controller
{
    public function index()
    {
        $rules = RiderIncentiveRule::latest()->get();
        $tipSettings = [
            'allow_custom_tips' => Setting::where('key', 'allow_custom_tips')->value('value') ?? '1',
            'suggested_tips' => Setting::where('key', 'suggested_tips')->value('value') ?? '10,20,50,100',
        ];

        return view('admin.incentives.index', compact('rules', 'tipSettings'));
    }

    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'target_deliveries' => 'required|integer|min:1',
            'bonus_amount' => 'required|numeric|min:1',
            'period_type' => 'required|string|in:daily,weekly,peak_hours',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        RiderIncentiveRule::create($validated);

        return redirect()->back()->with('success', 'Rider Incentive Rule created successfully!');
    }

    public function destroyRule(RiderIncentiveRule $rule)
    {
        $rule->delete();
        return redirect()->back()->with('success', 'Rider Incentive Rule deleted!');
    }

    public function updateTipSettings(Request $request)
    {
        $validated = $request->validate([
            'suggested_tips' => 'required|string',
            'allow_custom_tips' => 'boolean',
        ]);

        Setting::updateOrCreate(['key' => 'suggested_tips'], ['value' => $validated['suggested_tips']]);
        Setting::updateOrCreate(['key' => 'allow_custom_tips'], ['value' => $request->has('allow_custom_tips') ? '1' : '0']);

        return redirect()->back()->with('success', 'Customer Tip Settings saved!');
    }
}
