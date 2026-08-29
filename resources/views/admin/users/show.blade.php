@extends('admin.layouts.app')
@section('title', $user->name)
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $user->email }}</p>
            </div>
        </div>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary">Edit User</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto bg-gradient-to-br from-orange-400 to-orange-600 text-white text-xl font-semibold">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h3 class="font-semibold text-gray-900 mt-4">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                @php 
                $roleColors = ['super_admin' => 'badge-red', 'admin' => 'badge-red', 'store_owner' => 'badge-purple', 'store_manager' => 'badge-purple', 'customer' => 'badge-blue'];
                $roleValue = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
                @endphp
                <span class="badge {{ $roleColors[$roleValue] ?? 'badge-gray' }} mt-2">{{ $user->role instanceof \App\Enums\UserRole ? $user->role->label() : ucfirst($roleValue) }}</span>
            </div>
        </div>

        <div class="lg:col-span-2 card">
            <div class="card-header"><h3 class="card-title">User Details</h3></div>
            <div class="card-body">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900 mt-1">{{ $user->phone ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd class="mt-1">@if($user->is_active)<span class="badge badge-green">Active</span>@else<span class="badge badge-red">Inactive</span>@endif</dd></div>
                    <div><dt class="text-gray-500">Email Verified</dt><dd class="mt-1">@if($user->email_verified_at)<span class="badge badge-green">Verified</span>@else<span class="badge badge-orange">Unverified</span>@endif</dd></div>
                    <div><dt class="text-gray-500">Registered</dt><dd class="font-medium text-gray-900 mt-1">{{ \App\Helpers\DateHelper::format($user->created_at) }}</dd></div>
                    <div><dt class="text-gray-500">Last Login</dt><dd class="font-medium text-gray-900 mt-1">{{ $user->last_login_at?->diffForHumans() ?: 'Never' }}</dd></div>
                    <div><dt class="text-gray-500">Last Login IP</dt><dd class="font-medium text-gray-900 mt-1">{{ $user->last_login_ip ?: '—' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>

    @if($user->orders->count() > 0)
    <div class="card">
        <div class="card-header"><h3 class="card-title">Recent Orders</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Order</th>
                        <th class="table-header">Total</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($user->orders->take(5) as $order)
                    <tr class="hover:bg-gray-50/50">
                        <td class="table-cell font-mono font-semibold">#{{ $order->order_number }}</td>
                        <td class="table-cell"><x-currency :amount="$order->total" /></td>
                        <td class="table-cell"><span class="badge badge-{{ $order->status->color() }}">{{ $order->status->label() }}</span></td>
                        <td class="table-cell text-gray-500">{{ \App\Helpers\DateHelper::format($order->created_at) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
