@extends('admin.layouts.app')
@section('title', 'Withdrawal Requests')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Withdrawal Requests</h1>
            <p class="text-sm text-gray-500 mt-1">Review and process withdrawal requests from sellers and delivery boys</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-value text-xl text-orange-600">{{ $stats['pending_count'] ?? 0 }}</p>
            <p class="stat-label">Pending Requests</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-orange-600"><x-currency :amount="$stats['pending_amount'] ?? 0" /></p>
            <p class="stat-label">Pending Amount</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-green-600">{{ $stats['approved_today'] ?? 0 }}</p>
            <p class="stat-label">Approved Today</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-red-600">{{ $stats['rejected_today'] ?? 0 }}</p>
            <p class="stat-label">Rejected Today</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.wallets.withdrawals') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user name or email..." class="input pl-10">
            </div>
            <select name="status" class="input lg:w-40" onchange="this.form.submit()">
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
            </select>
            <input type="date" name="start_date" value="{{ request('start_date') }}" placeholder="Start Date" class="input lg:w-40">
            <input type="date" name="end_date" value="{{ request('end_date') }}" placeholder="End Date" class="input lg:w-40">
            <button type="submit" class="btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'start_date', 'end_date']) || $status !== 'pending')
            <a href="{{ route('admin.wallets.withdrawals') }}" class="btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">User</th>
                        <th class="table-header">Amount</th>
                        <th class="table-header">Bank Details</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Requested Date</th>
                        <th class="table-header">Processed</th>
                        <th class="table-header text-center w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($withdrawals as $withdrawal)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-gray-200 text-gray-600 text-xs font-semibold">
                                    {{ substr($withdrawal->wallet->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $withdrawal->wallet->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $withdrawal->wallet->user->email ?? 'N/A' }}</p>
                                    @php
                                    $roleValue = $withdrawal->wallet->user->role instanceof \BackedEnum ? $withdrawal->wallet->user->role->value : $withdrawal->wallet->user->role;
                                    @endphp
                                    <span class="badge badge-sm badge-blue mt-1">{{ ucwords(str_replace('_', ' ', $roleValue)) }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <span class="font-semibold text-gray-900 text-lg"><x-currency :amount="$withdrawal->amount" /></span>
                        </td>
                        <td class="table-cell">
                            <div class="text-sm">
                                <p class="font-medium text-gray-900">{{ $withdrawal->bank_name }}</p>
                                <p class="text-gray-600">{{ $withdrawal->account_holder_name }}</p>
                                <p class="text-gray-500 font-mono text-xs">{{ $withdrawal->account_number }}</p>
                                @if($withdrawal->ifsc_code)
                                <p class="text-gray-500 text-xs">IFSC: {{ $withdrawal->ifsc_code }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell">
                            @php
                            $statusColors = [
                                'pending' => 'badge-orange',
                                'approved' => 'badge-green',
                                'rejected' => 'badge-red',
                            ];
                            @endphp
                            <span class="badge {{ $statusColors[$withdrawal->status] ?? 'badge-gray' }}">{{ ucfirst($withdrawal->status) }}</span>
                            @if($withdrawal->status === 'rejected' && $withdrawal->rejection_reason)
                            <p class="text-xs text-red-600 mt-1 max-w-xs">{{ $withdrawal->rejection_reason }}</p>
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($withdrawal->created_at) }}</span>
                            <p class="text-xs text-gray-400">{{ \App\Helpers\DateHelper::formatTime($withdrawal->created_at) }}</p>
                        </td>
                        <td class="table-cell">
                            @if($withdrawal->processed_at)
                            <span class="text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($withdrawal->processed_at) }}</span>
                            <p class="text-xs text-gray-400">{{ $withdrawal->processedBy->name ?? 'N/A' }}</p>
                            @else
                            <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($withdrawal->status === 'pending')
                            <div class="flex items-center justify-center gap-1">
                                <form action="{{ route('admin.wallets.withdrawals.approve', $withdrawal) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="action-btn action-btn-view" title="Approve" onclick="return confirm('Are you sure you want to approve this withdrawal?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                <button type="button" class="action-btn action-btn-delete" title="Reject" onclick="openRejectModal({{ $withdrawal->id }})">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            @else
                            <div class="text-center text-gray-400 text-sm">-</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="empty-icon mx-auto">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <p class="font-medium text-gray-900 mt-4">No withdrawal requests found</p>
                            <p class="text-sm text-gray-500 mt-1">Withdrawal requests will appear here when users request withdrawals</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($withdrawals->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $withdrawals->appends(request()->query())->links() }}</div>@endif
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeRejectModal()"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Reject Withdrawal</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                    <textarea name="reason" rows="4" required class="input" placeholder="Enter reason for rejection..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">This reason will be visible to the user</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRejectModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary bg-red-500 hover:bg-red-600">Reject Withdrawal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openRejectModal(withdrawalId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/admin/wallets/withdrawals/${withdrawalId}/reject`;
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
    document.getElementById('rejectForm').reset();
}
</script>
@endpush
@endsection
