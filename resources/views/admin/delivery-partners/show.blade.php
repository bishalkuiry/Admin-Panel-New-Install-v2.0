@extends('admin.layouts.app')
@section('title', $deliveryPartner->name . ' - Overview')
@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.delivery-partners.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-wide">{{ $deliveryPartner->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Delivery Partner</span>
                    <span class="text-gray-300">&bull;</span>
                    <span class="text-xs font-medium text-gray-500">ID: #{{ str_pad($deliveryPartner->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>
        <div class="flex gap-3">
            @if(!$deliveryPartner->is_active || $deliveryPartner->kyc_status === 'pending')
            <form action="{{ route('admin.delivery-partners.approve', $deliveryPartner) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Approve Partner
                </button>
            </form>
            <form action="{{ route('admin.delivery-partners.reject', $deliveryPartner) }}" method="POST" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('Are you sure you want to reject this driver registration?')" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-bold shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reject Partner
                </button>
            </form>
            @else
            <form action="{{ route('admin.delivery-partners.toggle-status', $deliveryPartner) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 {{ $deliveryPartner->is_active ? 'bg-red-50 border-red-200 text-red-700 hover:bg-red-100' : 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100' }} border rounded-lg text-sm font-semibold transition-all">
                    @if($deliveryPartner->is_active)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Suspend Partner
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Reactivate Partner
                    @endif
                </button>
            </form>
            @endif
            <x-permission-btn 
                permission="delivery_partners.update" 
                href="{{ route('admin.delivery-partners.edit', $deliveryPartner) }}" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 text-sm font-semibold hover:bg-gray-50 transition-all shadow-none" 
                label="Edit Profile"
            />
        </div>
    </div>

    @include('admin.delivery-partners._tabs', ['deliveryPartner' => $deliveryPartner])

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card p-5 border border-gray-200 shadow-sm rounded-xl bg-white">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Deliveries</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_deliveries'] }}</div>
        </div>
        <div class="card p-5 border border-gray-200 shadow-sm rounded-xl bg-white">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Completed</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['completed_deliveries'] }}</div>
        </div>
        <div class="card p-5 border border-gray-200 shadow-sm rounded-xl bg-white">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pending</div>
            <div class="text-2xl font-bold text-amber-600">{{ $stats['pending_deliveries'] }}</div>
        </div>
        <div class="card p-5 border border-gray-200 shadow-sm rounded-xl bg-white">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Cancelled</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['cancelled_deliveries'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Column -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6 text-center">
                <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border-4 border-white shadow-sm ring-1 ring-gray-100">
                    @if($deliveryPartner->avatar)
                        <img src="{{ storage_url($deliveryPartner->avatar) }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-3xl font-bold text-gray-400">{{ substr($deliveryPartner->name, 0, 1) }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-bold text-gray-900">{{ $deliveryPartner->name }}</h2>
                <div class="flex items-center justify-center gap-2 mt-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide {{ $deliveryPartner->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $deliveryPartner->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="mt-8 space-y-5 text-left border-t border-gray-100 pt-6">
                    <div class="group">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-primary-600 transition-colors">Email Address</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $deliveryPartner->email }}</p>
                    </div>
                    <div class="group">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-primary-600 transition-colors">Phone Number</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $deliveryPartner->phone }}</p>
                    </div>
                    <div class="group">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-primary-600 transition-colors">Joined Date</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ \App\Helpers\DateHelper::format($deliveryPartner->created_at) }}</p>
                    </div>
                </div>
            </div>

            {{-- Complete Verification Documents (eKYC) Card --}}
            @php
                $kycSubmission = \App\Models\KycSubmission::where('user_id', $deliveryPartner->id)->latest()->first();
                $allKycFields = \App\Models\KycFormField::where('target_role', 'rider')->get();
            @endphp
            <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">eKYC Verification Profile</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Submitted verification details and uploaded documents</p>
                    </div>
                    @if($kycSubmission)
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wide {{ $kycSubmission->status === 'approved' ? 'bg-green-100 text-green-700' : ($kycSubmission->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800') }}">
                            {{ $kycSubmission->status }}
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">Pending Submission</span>
                    @endif
                </div>

                @if($kycSubmission && !empty($kycSubmission->data))
                    <div class="space-y-5">
                        @foreach($kycSubmission->data as $fieldKey => $fieldValue)
                            @php
                                $fieldObj = $allKycFields->firstWhere('field_name', $fieldKey);
                                $fieldLabel = $fieldObj ? $fieldObj->field_label : Str::title(str_replace('_', ' ', $fieldKey));
                                $isLocalPath = is_string($fieldValue) && (str_contains($fieldValue, '/data/user/') || str_contains($fieldValue, '/cache/') || str_contains($fieldValue, 'scaled_'));
                                $isPhoto = is_string($fieldValue) && (str_contains($fieldValue, '/storage/') || str_contains($fieldValue, 'http') || str_contains($fieldValue, '.jpg') || str_contains($fieldValue, '.png') || str_contains($fieldValue, '.jpeg'));
                            @endphp
                            <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">{{ $fieldLabel }}</label>
                                    <span class="text-[10px] font-mono text-gray-400">{{ $fieldKey }}</span>
                                </div>
                                @if($isLocalPath)
                                    <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs font-semibold text-amber-800 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>                                        Local device cached file path (Re-upload required from app for server storage)
                                    </div>
                                @elseif($isPhoto)
                                    <div class="mt-2 group relative overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                                        <img src="{{ $fieldValue }}" alt="{{ $fieldLabel }}" class="w-full h-48 object-cover">
                                        <a href="{{ $fieldValue }}" target="_blank" class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-white font-bold text-xs gap-1.5 backdrop-blur-xs">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View Original Document File
                                        </a>
                                    </div>
                                @else
                                    <p class="text-sm font-semibold text-gray-900 mt-1 bg-white p-2.5 rounded-lg border border-gray-200 font-mono">{{ $fieldValue }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-xs font-bold text-gray-500">No eKYC verification submission recorded</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Driver can complete document verification from the mobile app</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Activity Column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Recent Deliveries</h3>
                    <a href="{{ route('admin.delivery-partners.deliveries', $deliveryPartner) }}" class="text-xs font-bold text-primary-600 hover:text-primary-700 uppercase tracking-wider">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-white border-b border-gray-100 text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Order</th>
                                <th class="px-6 py-4">Store</th>
                                <th class="px-6 py-4">Fee</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($deliveryPartner->deliveries()->latest()->limit(5)->get() as $order)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-900 text-xs">#{{ $order->order_number }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-semibold text-gray-900">{{ $order->store->name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $order->store->city ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-gray-900">
                                    <x-currency :amount="$order->delivery_fee" />
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-1 rounded border border-gray-100 text-[10px] font-bold uppercase tracking-wider bg-gray-50 text-gray-600">
                                        {{ $order->status->value }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-xs text-gray-500 font-medium">
                                    {{ \App\Helpers\DateHelper::format($order->created_at, false) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-xs text-gray-500 italic">
                                    No delivery activity recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
