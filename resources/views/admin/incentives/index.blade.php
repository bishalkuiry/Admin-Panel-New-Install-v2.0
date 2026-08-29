@extends('admin.layouts.app')

@section('title', 'Rider Incentives & Customer Tips')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Rider Incentives & Customer Tips</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Configure performance bonus rules for delivery partners & customer tipping settings</p>
        </div>
        <button type="button" @click="$dispatch('open-add-rule-modal')" class="btn btn-primary text-xs flex items-center justify-center gap-1.5 w-full sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Incentive Rule</span>
        </button>
    </div>

    <!-- Customer Tip Settings Card -->
    <div class="card p-4 sm:p-5">
        <h3 class="font-bold text-sm text-gray-900 border-b pb-2 mb-3">Customer Tipping Configurations (100% Passed to Rider)</h3>
        <form action="{{ route('admin.incentives.tips.update') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            @csrf
            <div>
                <label class="label font-bold text-gray-800">Suggested Tip Options (Comma Separated, ₹)</label>
                <input type="text" name="suggested_tips" value="{{ $tipSettings['suggested_tips'] }}" class="input text-xs font-mono" placeholder="10,20,50,100">
            </div>
            <div class="flex items-center pt-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="allow_custom_tips" value="1" {{ $tipSettings['allow_custom_tips'] == '1' ? 'checked' : '' }} class="rounded text-orange-500">
                    <span class="font-bold text-gray-800">Allow Custom Tip Input Amount</span>
                </label>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary text-xs w-full">Save Tip Config</button>
            </div>
        </form>
    </div>

    <!-- Incentive Rules Table -->
    <div class="card overflow-hidden p-4 sm:p-5 space-y-3">
        <h3 class="font-bold text-sm text-gray-900 border-b pb-2">Active Rider Incentive Bonus Rules</h3>
        <div class="overflow-x-auto">
            <table class="table w-full text-xs min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Rule Title</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Target Deliveries</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Bonus Cash Payout</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Period</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Status</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rules as $rule)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4">
                                <p class="font-bold text-gray-900">{{ $rule->title }}</p>
                                <p class="text-[10px] text-gray-500">{{ $rule->description }}</p>
                            </td>
                            <td class="py-3 px-4 font-bold text-gray-800">{{ $rule->target_deliveries }} orders</td>
                            <td class="py-3 px-4 font-extrabold text-green-700">₹{{ number_format($rule->bonus_amount, 2) }}</td>
                            <td class="py-3 px-4 uppercase font-bold text-[10px] text-gray-600">{{ $rule->period_type }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $rule->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $rule->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('admin.incentives.rules.destroy', $rule->id) }}" method="POST" onsubmit="return confirm('Delete this incentive rule?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-400">No incentive rules created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Rule Modal -->
<div x-data="{ open: false }" @open-add-rule-modal.window="open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-sm text-gray-900">Add Rider Incentive Rule</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.incentives.rules.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="label font-bold">Rule Title</label>
                    <input type="text" name="title" class="input text-xs" required placeholder="e.g. Daily Target Bonus 10 Deliveries">
                </div>
                <div>
                    <label class="label font-bold">Description</label>
                    <input type="text" name="description" class="input text-xs" placeholder="Complete 10 deliveries today to earn ₹200 bonus">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="label font-bold">Target Deliveries</label>
                        <input type="number" name="target_deliveries" class="input text-xs" required value="10">
                    </div>
                    <div>
                        <label class="label font-bold">Bonus Payout (₹)</label>
                        <input type="number" step="0.01" name="bonus_amount" class="input text-xs" required value="200.00">
                    </div>
                </div>
                <div>
                    <label class="label font-bold">Period Type</label>
                    <select name="period_type" class="input text-xs">
                        <option value="daily">Daily Target</option>
                        <option value="weekly">Weekly Target</option>
                        <option value="peak_hours">Peak Hours Bonus</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" value="1" checked id="is_act" class="rounded text-orange-500">
                    <label for="is_act" class="font-bold text-gray-800">Rule Active Immediately</label>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="open = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Save Incentive Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
