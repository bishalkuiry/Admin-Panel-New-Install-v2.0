@extends('admin.layouts.app')
@section('title', 'Order Chat - #' . $order->order_number)

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders.show', $order) }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">
                    {{ $chatType === 'customer_delivery' ? 'Customer ↔ Delivery Partner' : 'Customer ↔ Seller' }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">Order #{{ $order->order_number }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
        <!-- Chat Area -->
        <div class="lg:col-span-3">
            <div class="card" style="height: calc(100vh - 200px); display: flex; flex-direction: column;">
                <div class="card-header border-b">
                    <div class="flex items-center gap-3">
                        <div class="avatar bg-gray-200 text-gray-600 font-semibold">
                            {{ substr($chat->customer->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">{{ $chat->customer->name }}</h3>
                            <p class="text-xs text-gray-500">Customer</p>
                        </div>
                        <div class="mx-4 text-gray-400">↔</div>
                        @if($chat->participant)
                        <div class="avatar bg-gray-200 text-gray-600 font-semibold">
                            {{ substr($chat->participant->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">{{ $chat->participant->name }}</h3>
                            <p class="text-xs text-gray-500">{{ $chatType === 'customer_delivery' ? 'Delivery Partner' : 'Seller' }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Messages Container -->
                <div id="messages-container" class="flex-1 overflow-y-auto p-4 bg-gray-50" style="scroll-behavior: smooth;">
                    <div id="messages-list" class="space-y-3">
                        <!-- Messages will be loaded here -->
                    </div>
                </div>

                <!-- Input Area -->
                <div class="card-footer border-t bg-white p-4">
                    <div class="flex gap-3">
                        <input 
                            type="text" 
                            id="message-input" 
                            class="input flex-1" 
                            placeholder="Type a message as admin..."
                        />
                        <button id="send-button" class="btn-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Participants Info -->
        <div class="space-y-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Customer</h3></div>
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="avatar bg-gray-200 text-gray-600 font-semibold">
                            {{ substr($chat->customer->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $chat->customer->name }}</p>
                            <p class="text-sm text-gray-500">{{ $order->user->email }}</p>
                        </div>
                    </div>
                    @if($order->phone)
                    <p class="text-sm text-gray-600">
                        <span class="text-gray-500">Phone:</span> {{ $order->phone }}
                    </p>
                    @endif
                </div>
            </div>

            @if($chat->participant)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $chatType === 'customer_delivery' ? 'Delivery Partner' : 'Seller' }}</h3>
                </div>
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="avatar bg-gray-200 text-gray-600 font-semibold">
                            {{ substr($chat->participant->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $chat->participant->name }}</p>
                            <p class="text-sm text-gray-500">{{ $chat->participant->email }}</p>
                        </div>
                    </div>
                    @if($chat->participant->phone)
                    <p class="text-sm text-gray-600">
                        <span class="text-gray-500">Phone:</span> {{ $chat->participant->phone }}
                    </p>
                    @endif
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">
                            {{ $chatType === 'customer_delivery' ? 'No delivery partner assigned yet' : 'No seller assigned yet' }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">Order Info</h3></div>
                <div class="card-body space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Order Number</span>
                        <span class="text-gray-900 font-medium">{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="badge badge-orange">{{ ucfirst(str_replace('_', ' ', $order->status->value)) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total</span>
                        <span class="text-gray-900 font-medium">{{ \App\Helpers\CurrencyHelper::format($order->total) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
import { getDatabase, ref, onValue, push, serverTimestamp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js';

// Firebase configuration from settings
const firebaseConfig = {
    apiKey: "{{ config('services.firebase.api_key') }}",
    authDomain: "{{ config('services.firebase.auth_domain') }}",
    databaseURL: "{{ config('services.firebase.database_url') }}",
    projectId: "{{ config('services.firebase.project_id') }}",
    storageBucket: "{{ config('services.firebase.storage_bucket') }}",
    messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
    appId: "{{ config('services.firebase.app_id') }}"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const database = getDatabase(app);

const chatId = '{{ $chat->firebase_chat_id }}';
const messagesRef = ref(database, `chats/order_chats/${chatId}/messages`);
const adminId = {{ $chat->admin_id ?? Auth::id() ?? 1 }};
const adminName = '{{ $chat->admin?->name ?? Auth::user()?->name ?? "Admin" }}';

console.log('🔥 Firebase Admin Chat Initialized', {
    chatId,
    adminId,
    adminName,
    databaseURL: firebaseConfig.databaseURL,
    messagesPath: `chats/order_chats/${chatId}/messages`
});

// Listen to messages
onValue(messagesRef, (snapshot) => {
    console.log('📨 Messages snapshot received', {
        exists: snapshot.exists(),
        numChildren: snapshot.size
    });
    
    const messages = [];
    snapshot.forEach((childSnapshot) => {
        const message = childSnapshot.val();
        message.id = childSnapshot.key;
        messages.push(message);
    });

    console.log('📋 Messages loaded:', messages.length);

    // Sort by timestamp
    messages.sort((a, b) => a.timestamp - b.timestamp);

    // Display messages
    displayMessages(messages);
}, (error) => {
    console.error('❌ Firebase error:', error);
    alert('Failed to connect to chat: ' + error.message);
});

function displayMessages(messages) {
    const container = document.getElementById('messages-list');
    container.innerHTML = '';

    messages.forEach(message => {
        const isAdmin = message.sender_id === adminId;
        const isCustomer = message.sender_id === {{ $chat->customer_id }};
        
        let senderLabel = message.sender_name;
        let bgColor = 'bg-gray-200';
        let textColor = 'text-gray-900';
        
        if (isAdmin) {
            senderLabel = 'Admin: ' + message.sender_name;
            bgColor = 'bg-purple-100';
            textColor = 'text-purple-900';
        } else if (isCustomer) {
            bgColor = 'bg-blue-100';
            textColor = 'text-blue-900';
        } else {
            bgColor = 'bg-green-100';
            textColor = 'text-green-900';
        }

        const messageEl = document.createElement('div');
        messageEl.className = 'flex flex-col';
        messageEl.innerHTML = `
            <div class="flex items-start gap-2 ${isAdmin ? 'flex-row-reverse' : ''}">
                <div class="avatar avatar-sm ${bgColor} ${textColor} font-semibold">
                    ${message.sender_name.charAt(0).toUpperCase()}
                </div>
                <div class="flex-1 ${isAdmin ? 'text-right' : ''}">
                    <div class="inline-block max-w-md p-3 rounded-lg ${bgColor}">
                        <p class="text-xs font-medium ${textColor} mb-1">${senderLabel}</p>
                        <p class="text-sm ${textColor}">${escapeHtml(message.message)}</p>
                        <p class="text-xs text-gray-500 mt-1">${formatTime(message.timestamp)}</p>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(messageEl);
    });

    // Scroll to bottom
    const messagesContainer = document.getElementById('messages-container');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

async function sendMessage() {
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if (!message) return;

    console.log('📤 Sending message:', message);

    try {
        // Send to Firebase
        const newMessage = {
            sender_id: adminId,
            sender_name: adminName,
            sender_role: 'admin',
            message: message,
            message_type: 'text',
            timestamp: serverTimestamp(),
            is_read: false
        };
        
        console.log('💾 Pushing to Firebase:', newMessage);
        await push(messagesRef, newMessage);
        console.log('✅ Message sent to Firebase');

        // Clear input
        input.value = '';

        // Notify backend - Admin messages always send notifications
        const notifyResponse = await fetch('/admin/orders/{{ $order->id }}/chat/{{ $chatType }}/notify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                message: message,
                admin_id: adminId
            })
        });
        
        if (!notifyResponse.ok) {
            console.error('Failed to send notification:', notifyResponse.status);
        }
    } catch (error) {
        console.error('❌ Error sending message:', error);
        alert('Failed to send message: ' + error.message);
    }
}

// Add event listeners
document.getElementById('send-button').addEventListener('click', sendMessage);
document.getElementById('message-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

function formatTime(timestamp) {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection

