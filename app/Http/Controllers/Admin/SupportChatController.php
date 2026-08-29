<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportChat;
use App\Models\SupportChatMessage;
use App\Services\StorageService;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function __construct(private StorageService $storage) {}

    public function index(Request $request)
    {
        $chats = SupportChat::with(['user', 'messages'])->latest('last_message_at')->paginate(20);
        $activeChat = null;
        
        if ($request->filled('chat_id')) {
            $activeChat = SupportChat::with(['user', 'messages.sender'])->find($request->chat_id);
            if ($activeChat) {
                // Mark unread messages as read
                SupportChatMessage::where('chat_id', $activeChat->id)
                    ->where('sender_id', '!=', auth()->id())
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }

        return view('admin.chat.index', compact('chats', 'activeChat'));
    }

    public function sendMessage(Request $request, SupportChat $chat)
    {
        $validated = $request->validate([
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:10240',
        ]);

        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $attachmentUrl = $this->storage->store($request->file('attachment'), 'chat_attachments');
        }

        if (empty($validated['message']) && !$attachmentUrl) {
            return redirect()->back()->with('error', 'Cannot send empty message.');
        }

        SupportChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => auth()->id(),
            'message' => $validated['message'],
            'attachment_url' => $attachmentUrl,
        ]);

        $chat->update([
            'last_message_at' => now(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('admin.support-chat.index', ['chat_id' => $chat->id]);
    }
}
