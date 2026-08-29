@extends('admin.layouts.app')

@section('title', 'Live Customer Support Chat')

@section('content')
<div class="flex flex-col lg:flex-row gap-4 h-[calc(100vh-140px)]">
    <!-- Left Column: Chat Threads List -->
    <div class="w-full lg:w-80 card p-4 flex flex-col space-y-3 overflow-hidden">
        <h2 class="font-bold text-sm text-gray-900 border-b pb-2 flex items-center justify-between">
            <span>Support Conversations</span>
            <span class="badge badge-orange font-mono text-[10px]">{{ $chats->total() }}</span>
        </h2>

        <div class="flex-1 overflow-y-auto space-y-2 pr-1">
            @forelse($chats as $c)
                <a href="{{ route('admin.support-chat.index', ['chat_id' => $c->id]) }}" class="block p-3 rounded-xl border transition-all {{ $activeChat && $activeChat->id === $c->id ? 'bg-orange-50/70 border-orange-300' : 'bg-gray-50 border-gray-100 hover:bg-gray-100' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-900 truncate">{{ $c->user?->name ?? 'User #'.$c->user_id }}</span>
                        <span class="text-[9px] text-gray-400 font-mono">{{ $c->last_message_at?->format('h:i A') }}</span>
                    </div>
                    <p class="text-[11px] text-gray-500 line-clamp-1 mt-0.5">{{ $c->subject }}</p>
                </a>
            @empty
                <div class="py-12 text-center text-xs text-gray-400">No active support conversations.</div>
            @endforelse
        </div>
    </div>

    <!-- Right Column: Live Chat Messenger Box -->
    <div class="flex-1 card p-4 sm:p-5 flex flex-col justify-between space-y-3 overflow-hidden">
        @if($activeChat)
            <!-- Chat Header -->
            <div class="flex items-center justify-between border-b pb-3">
                <div>
                    <h3 class="font-bold text-base text-gray-900">{{ $activeChat->user?->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $activeChat->user?->email }} | {{ $activeChat->user?->phone }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-green-100 text-green-700">
                    Online Support
                </span>
            </div>

            <!-- Messages Conversation History -->
            <div class="flex-1 overflow-y-auto space-y-3 p-2 bg-gray-50/50 rounded-xl border border-gray-100 max-h-[calc(100vh-280px)]">
                @foreach($activeChat->messages as $msg)
                    @php $isAdmin = $msg->sender_id === auth()->id(); @endphp
                    <div class="flex flex-col {{ $isAdmin ? 'items-end' : 'items-start' }}">
                        <div class="max-w-md p-3 rounded-2xl text-xs {{ $isAdmin ? 'bg-orange-600 text-white rounded-br-none' : 'bg-white text-gray-900 border border-gray-200 rounded-bl-none shadow-sm' }}">
                            <p class="font-bold text-[10px] opacity-75 mb-0.5">{{ $msg->sender?->name }}</p>
                            @if($msg->message)
                                <p class="leading-relaxed">{{ $msg->message }}</p>
                            @endif
                            @if($msg->attachment_url)
                                <div class="mt-2 pt-2 border-t border-white/20">
                                    <a href="{{ storage_url($msg->attachment_url) }}" target="_blank" class="inline-flex items-center gap-1 font-bold underline text-[11px]">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <span>View Attachment</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 text-[9px] text-gray-400 mt-1 px-1">
                            <span>{{ $msg->created_at?->format('h:i A') }}</span>
                            @if($isAdmin)
                                <span>• {{ $msg->read_at ? 'Read ✓✓' : 'Delivered ✓' }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Send Message Form -->
            <form action="{{ route('admin.support-chat.send', $activeChat->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 pt-2 border-t">
                @csrf
                <div class="flex-1 relative">
                    <input type="text" name="message" class="input text-xs pr-10" placeholder="Type message reply to customer...">
                    <label class="absolute right-2 top-2 text-gray-400 hover:text-orange-500 cursor-pointer p-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <input type="file" name="attachment" class="hidden">
                    </label>
                </div>
                <button type="submit" class="btn btn-primary text-xs py-2.5 px-4 font-bold flex items-center gap-1.5">
                    <span>Send</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-center text-gray-400 p-8">
                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <p class="font-bold text-sm text-gray-700">Select a conversation from the left to start live chatting</p>
            </div>
        @endif
    </div>
</div>
@endsection
