@extends('admin.layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Support Tickets</h1>
            <p class="text-sm text-gray-500 mt-1">Manage customer support requests</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach([
            ['label'=>'Open',         'key'=>'open',         'color'=>'blue'],
            ['label'=>'In Progress',  'key'=>'in_progress',  'color'=>'yellow'],
            ['label'=>'Waiting User', 'key'=>'waiting_user', 'color'=>'orange'],
            ['label'=>'Resolved',     'key'=>'resolved',     'color'=>'green'],
            ['label'=>'Closed',       'key'=>'closed',       'color'=>'gray'],
        ] as $s)
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-{{ $s['color'] }}-600">{{ $stats[$s['key']] }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $s['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                <option value="">All</option>
                @foreach(['open','in_progress','waiting_user','resolved','closed'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
            <select name="category_id" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                <option value="">All</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category_id')==$cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket # or subject…"
                   class="text-sm border border-gray-300 rounded-lg px-3 py-2 w-48">
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Filter</button>
        <a href="{{ route('admin.support.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Reset</a>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Ticket</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">User</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Category</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Priority</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Assigned</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Updated</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tickets as $ticket)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-900">{{ $ticket->ticket_number }}</div>
                        <div class="text-xs text-gray-500 truncate max-w-[180px]">{{ $ticket->subject }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $ticket->user->name }}</div>
                        <div class="text-xs text-gray-400">{{ $ticket->user->email }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $ticket->category->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @php $colors = ['open'=>'blue','in_progress'=>'yellow','waiting_user'=>'orange','resolved'=>'green','closed'=>'gray']; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $colors[$ticket->status] ?? 'gray' }}-100 text-{{ $colors[$ticket->status] ?? 'gray' }}-700">
                            {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @php $pc = ['low'=>'gray','medium'=>'blue','high'=>'orange','urgent'=>'red']; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $pc[$ticket->priority] ?? 'gray' }}-100 text-{{ $pc[$ticket->priority] ?? 'gray' }}-700">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $ticket->assignedAgent->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $ticket->updated_at->diffForHumans() }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.support.show', $ticket) }}"
                           class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">View →</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $tickets->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
