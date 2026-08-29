@extends('admin.layouts.app')

@section('title', 'Ticket ' . $ticket->ticket_number)

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.support.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $ticket->ticket_number }}</h1>
            <p class="text-sm text-gray-500">{{ $ticket->subject }}</p>
        </div>
        <div class="ml-auto flex gap-2">
            {{-- Live Chat button --}}
            <button onclick="openLiveChat()" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Live Chat
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Messages --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                @foreach($ticket->messages as $msg)
                <div class="p-4 {{ $msg->is_admin ? 'bg-indigo-50' : '' }}">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                            {{ strtoupper(substr($msg->user->name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <span class="font-semibold text-gray-800 text-sm">{{ $msg->user->name ?? 'Unknown' }}</span>
                            @if($msg->is_admin)
                            <span class="ml-2 px-1.5 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded font-medium">Support Agent</span>
                            @endif
                        </div>
                        <span class="ml-auto text-xs text-gray-400">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed">{{ $msg->message }}</p>
                    @if($msg->attachments)
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($msg->attachments as $att)
                        <a href="{{ $att }}" target="_blank" class="text-xs text-indigo-600 underline">📎 Attachment</a>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Reply form --}}
            @if($ticket->isOpen())
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Reply</h3>
                <form action="{{ route('admin.support.reply', $ticket) }}" method="POST">
                    @csrf
                    <textarea name="message" rows="4" required placeholder="Type your reply…"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                    <div class="flex items-center gap-3 mt-3">
                        <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Keep current status</option>
                            <option value="in_progress">Mark In Progress</option>
                            <option value="waiting_user">Waiting for User</option>
                            <option value="resolved">Mark Resolved</option>
                            <option value="closed">Close Ticket</option>
                        </select>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 text-center text-sm text-gray-500">
                This ticket is {{ $ticket->status }}. Reopen it to reply.
                <form action="{{ route('admin.support.status', $ticket) }}" method="POST" class="inline ml-2">
                    @csrf
                    <input type="hidden" name="status" value="open">
                    <button type="submit" class="text-indigo-600 underline text-sm">Reopen</button>
                </form>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">

            {{-- Ticket Info --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                <h3 class="font-semibold text-gray-800">Ticket Details</h3>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between"><span class="text-gray-500">Status</span>
                        <span class="font-medium text-gray-800">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    </div>
                    <div class="flex justify-between"><span class="text-gray-500">Priority</span>
                        <span class="font-medium text-gray-800">{{ ucfirst($ticket->priority) }}</span>
                    </div>
                    <div class="flex justify-between"><span class="text-gray-500">Category</span>
                        <span class="font-medium text-gray-800">{{ $ticket->category->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between"><span class="text-gray-500">Created</span>
                        <span class="font-medium text-gray-800">{{ $ticket->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- User Info --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-2">
                <h3 class="font-semibold text-gray-800">Customer</h3>
                <div class="text-sm">
                    <div class="font-medium text-gray-800">{{ $ticket->user->name }}</div>
                    <div class="text-gray-500">{{ $ticket->user->email }}</div>
                    @if($ticket->user->phone)
                    <div class="text-gray-500">{{ $ticket->user->phone }}</div>
                    @endif
                </div>
            </div>

            {{-- Assign Agent --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Assign Agent</h3>
                <form action="{{ route('admin.support.assign', $ticket) }}" method="POST" class="flex gap-2">
                    @csrf
                    <select name="agent_id" class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Select agent</option>
                        @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" @selected($ticket->assigned_to == $agent->id)>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">Assign</button>
                </form>
            </div>

            {{-- Quick Status --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Change Status</h3>
                <form action="{{ route('admin.support.status', $ticket) }}" method="POST" class="flex gap-2">
                    @csrf
                    <select name="status" class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2">
                        @foreach(['open','in_progress','waiting_user','resolved','closed'] as $s)
                        <option value="{{ $s }}" @selected($ticket->status===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-800">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Live Chat Modal --}}
<div id="liveChatModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg h-[600px] flex flex-col shadow-2xl">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <div>
                <div class="font-semibold text-gray-900">Live Chat — {{ $ticket->ticket_number }}</div>
                <div class="text-xs text-gray-500">{{ $ticket->user->name }}</div>
            </div>
            <button onclick="closeLiveChat()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
            <div class="text-center text-xs text-gray-400">Loading messages…</div>
        </div>
        <div class="p-3 border-t border-gray-200 flex gap-2">
            <input id="chatInput" type="text" placeholder="Type a message…"
                   class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                   onkeydown="if(event.key==='Enter') sendAdminMessage()">
            <button onclick="sendAdminMessage()" class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center hover:bg-indigo-700">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </div>
    </div>
</div>

@push('scripts')
{{-- Firebase JS SDK — real-time WebSocket to Firebase (no polling, no PHP load) --}}
<script type="module">
import { initializeApp, getApps, deleteApp } from 'https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js';
import { getDatabase, ref, onChildAdded }
    from 'https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js';

@php
    // Read all Firebase web SDK config from the settings table — nothing hardcoded
    use App\Models\Setting;
    $fbSettings = Setting::whereIn('key', [
        'firebase_api_key', 'firebase_auth_domain', 'firebase_database_url',
        'firebase_project_id', 'firebase_storage_bucket',
        'firebase_messaging_sender_id', 'firebase_app_id',
    ])->pluck('value', 'key');

    $fbProjectId         = $fbSettings->get('firebase_project_id', '');
    $fbApiKey            = $fbSettings->get('firebase_api_key', '');
    $fbAuthDomain        = $fbSettings->get('firebase_auth_domain', $fbProjectId ? "{$fbProjectId}.firebaseapp.com" : '');
    $fbDatabaseUrl       = rtrim($fbSettings->get('firebase_database_url', ''), '/');
    $fbStorageBucket     = $fbSettings->get('firebase_storage_bucket', $fbProjectId ? "{$fbProjectId}.firebasestorage.app" : '');
    $fbMessagingSenderId = $fbSettings->get('firebase_messaging_sender_id', '');
    $fbAppId             = $fbSettings->get('firebase_app_id', '');
    $fbConfigured        = $fbApiKey && $fbProjectId && $fbDatabaseUrl;
@endphp

@if($fbConfigured)
// ── Firebase config — all values from admin settings, nothing hardcoded ──────
const FIREBASE_CONFIG = {
    apiKey:            @json($fbApiKey),
    authDomain:        @json($fbAuthDomain),
    databaseURL:       @json($fbDatabaseUrl),
    projectId:         @json($fbProjectId),
    storageBucket:     @json($fbStorageBucket),
    messagingSenderId: @json($fbMessagingSenderId),
    appId:             @json($fbAppId),
};
@else
const FIREBASE_CONFIG = null; // Not fully configured yet
@endif

const CHAT_ID  = '{{ $ticket->firebase_chat_id }}';
const ADMIN_ID = {{ auth()->id() }};
const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
const API_SEND = '{{ route("admin.support.chat.send", $ticket) }}';
const APP_NAME = 'admin-support-{{ $ticket->id }}';

let fbApp       = null;
let db          = null;
let msgRef      = null;
let unsubscribe = null;
let isOpen      = false;

// ── Init Firebase ─────────────────────────────────────────────────────────────
async function initFirebase() {
    const existing = getApps().find(a => a.name === APP_NAME);
    if (existing) await deleteApp(existing);
    fbApp  = initializeApp(FIREBASE_CONFIG, APP_NAME);
    db     = getDatabase(fbApp);
    msgRef = ref(db, `chats/support_chats/${CHAT_ID}/messages`);
}

// ── Append a single message bubble ───────────────────────────────────────────
function appendMessage(msg) {
    const container = document.getElementById('chatMessages');
    const placeholder = container.querySelector('[data-placeholder]');
    if (placeholder) placeholder.remove();

    const isMe = msg.sender_id === ADMIN_ID;
    const time = msg.timestamp
        ? new Date(msg.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        : '';

    const bubble = document.createElement('div');
    bubble.className = `flex ${isMe ? 'justify-end' : 'justify-start'} mb-2`;
    bubble.dataset.msg = msg.id; // used to skip duplicates on re-subscribe

    const inner = document.createElement('div');
    inner.className = `max-w-[75%] px-3 py-2 rounded-2xl text-sm shadow-sm ${
        isMe ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-800'
    }`;

    if (!isMe) {
        const name = document.createElement('div');
        name.className = 'text-xs font-semibold mb-1 text-indigo-600';
        name.textContent = msg.sender_name || 'User';
        inner.appendChild(name);
    }

    const text = document.createElement('div');
    text.textContent = msg.message ?? ''; // textContent = XSS-safe
    inner.appendChild(text);

    const ts = document.createElement('div');
    ts.className = `text-[10px] mt-1 ${isMe ? 'text-indigo-200 text-right' : 'text-gray-400'}`;
    ts.textContent = time;
    inner.appendChild(ts);

    bubble.appendChild(inner);
    document.getElementById('chatMessages').appendChild(bubble);
    document.getElementById('chatMessages').scrollTop = 99999;
}

// ── Start real-time listener ──────────────────────────────────────────────────
function startListener() {
    if (!CHAT_ID || !msgRef || unsubscribe) return;
    // Only reset the container on first open, not on tab-visibility resume
    if (!document.getElementById('chatMessages').querySelector('[data-msg]')) {
        document.getElementById('chatMessages').innerHTML =
            '<div data-placeholder class="text-center text-xs text-gray-400 py-8">No messages yet. Start the conversation!</div>';
    }
    // onChildAdded returns an unsubscribe function directly (modular SDK)
    unsubscribe = onChildAdded(msgRef, (snapshot) => {
        const msg = snapshot.val();
        if (!msg) return;
        // Skip if bubble already rendered (prevents duplicates on re-subscribe)
        if (document.querySelector(`[data-msg="${snapshot.key}"]`)) return;
        appendMessage({
            id:          snapshot.key,
            sender_id:   msg.sender_id   ?? 0,
            sender_name: msg.sender_name  ?? 'User',
            message:     msg.message      ?? '',
            timestamp:   typeof msg.timestamp === 'number' ? msg.timestamp : Date.now(),
        });
    });
}

// ── Stop listener ─────────────────────────────────────────────────────────────
function stopListener() {
    if (unsubscribe) {
        unsubscribe(); // modular SDK: onChildAdded returns the unsubscribe fn directly
        unsubscribe = null;
    }
}

// ── Open ──────────────────────────────────────────────────────────────────────
window.openLiveChat = async function () {
    if (!FIREBASE_CONFIG) {
        alert('Firebase Web SDK is not configured.\n\nGo to Settings → Mobile App → Firebase and fill in the Web SDK Config fields (API Key, App ID, etc.).');
        return;
    }
    if (!CHAT_ID) {
        alert('This ticket has no Firebase chat ID. Please re-create the ticket.');
        return;
    }
    isOpen = true;
    document.getElementById('liveChatModal').classList.remove('hidden');
    document.getElementById('chatInput').focus();
    if (!fbApp) await initFirebase();
    startListener();
};

// ── Close ─────────────────────────────────────────────────────────────────────
window.closeLiveChat = function () {
    isOpen = false;
    stopListener();
    document.getElementById('liveChatModal').classList.add('hidden');
};

// ── Send ──────────────────────────────────────────────────────────────────────
window.sendAdminMessage = async function () {
    const input = document.getElementById('chatInput');
    const text  = input.value.trim();
    if (!text) return;
    input.value    = '';
    input.disabled = true;
    try {
        const res = await fetch(API_SEND, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ message: text }),
        });
        if (!res.ok) input.value = text;
    } catch (e) {
        console.error('Send failed', e);
        input.value = text;
    } finally {
        input.disabled = false;
        input.focus();
    }
};

// Pause when tab hidden, resume when visible
document.addEventListener('visibilitychange', () => {
    if (!isOpen) return;
    document.hidden ? stopListener() : startListener();
});
</script>
@endpush
@endsection
