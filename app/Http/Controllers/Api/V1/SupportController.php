<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SupportCategory;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupportController extends Controller
{
    // ── GET /api/v1/support/categories ──────────────────────────────────────
    public function categories()
    {
        $categories = SupportCategory::active()->get(['id', 'name', 'icon', 'description']);
        return response()->json(['success' => true, 'data' => $categories]);
    }

    // ── GET /api/v1/support/tickets ──────────────────────────────────────────
    public function index()
    {
        $tickets = SupportTicket::forUser(Auth::id())
            ->with(['category:id,name,icon', 'latestMessage'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $tickets]);
    }

    // ── POST /api/v1/support/tickets ─────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'category_id'  => 'required|exists:support_categories,id',
            'subject'      => 'required|string|max:200',
            'description'  => 'required|string|max:2000',
            'attachments'  => 'nullable|array|max:5',
            'attachments.*'=> 'file|mimes:jpg,jpeg,png,gif,pdf,mp4,mov|max:20480',
        ]);

        $ticket = SupportTicket::create([
            'user_id'     => Auth::id(),
            'category_id' => $request->category_id,
            'subject'     => $request->subject,
            'description' => $request->description,
            'status'      => 'open',
            'priority'    => 'medium',
        ]);

        // Store initial message with attachments
        $attachmentPaths = $this->storeAttachments($request, $ticket->id);

        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'message'     => $request->description,
            'attachments' => $attachmentPaths ?: null,
            'is_admin'    => false,
        ]);

        $ticket->load('category:id,name,icon');

        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully.',
            'data'    => $this->formatTicket($ticket),
        ], 201);
    }

    // ── GET /api/v1/support/tickets/{ticket} ─────────────────────────────────
    public function show(SupportTicket $ticket)
    {
        $this->authorizeUser($ticket);

        $ticket->load(['category:id,name,icon', 'messages.user:id,name,avatar', 'assignedAgent:id,name,avatar']);

        // Mark all admin messages as read
        $ticket->messages()
            ->where('is_admin', true)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'data'    => $this->formatTicket($ticket, true),
        ]);
    }

    // ── POST /api/v1/support/tickets/{ticket}/messages ───────────────────────
    public function sendMessage(Request $request, SupportTicket $ticket)
    {
        $this->authorizeUser($ticket);

        if (!$ticket->isOpen()) {
            return response()->json(['success' => false, 'message' => 'Ticket is closed.'], 422);
        }

        $request->validate([
            'message'      => 'required|string|max:2000',
            'attachments'  => 'nullable|array|max:5',
            'attachments.*'=> 'file|mimes:jpg,jpeg,png,gif,pdf,mp4,mov|max:20480',
        ]);

        $attachmentPaths = $this->storeAttachments($request, $ticket->id);

        $message = SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'message'     => $request->message,
            'attachments' => $attachmentPaths ?: null,
            'is_admin'    => false,
        ]);

        // Update ticket status back to open if it was waiting_user
        if ($ticket->status === 'waiting_user') {
            $ticket->update(['status' => 'in_progress']);
        }

        $ticket->touch();

        return response()->json([
            'success' => true,
            'data'    => $this->formatMessage($message),
        ], 201);
    }

    // ── POST /api/v1/support/tickets/{ticket}/chat/init ──────────────────────
    // Returns Firebase chat ID so the app can open live chat
    public function initChat(SupportTicket $ticket)
    {
        $this->authorizeUser($ticket);

        if (!$ticket->isOpen()) {
            return response()->json(['success' => false, 'message' => 'Ticket is closed.'], 422);
        }

        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data'    => [
                'ticket_id'        => $ticket->id,
                'ticket_number'    => $ticket->ticket_number,
                'firebase_chat_id' => $ticket->firebase_chat_id,
                'subject'          => $ticket->subject,
                'status'           => $ticket->status,
                'user' => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'avatar' => $user->avatar ? storage_url($user->avatar) : null,
                ],
                'agent' => $ticket->assignedAgent ? [
                    'id'     => $ticket->assignedAgent->id,
                    'name'   => $ticket->assignedAgent->name,
                    'avatar' => $ticket->assignedAgent->avatar
                        ? storage_url($ticket->assignedAgent->avatar)
                        : null,
                ] : null,
            ],
        ]);
    }

    // ── POST /api/v1/support/tickets/{ticket}/close ──────────────────────────
    public function close(SupportTicket $ticket)
    {
        $this->authorizeUser($ticket);

        if ($ticket->status === 'closed') {
            return response()->json(['success' => true, 'message' => 'Ticket already closed.']);
        }

        $ticket->update(['status' => 'closed', 'closed_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Ticket closed.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function authorizeUser(SupportTicket $ticket): void
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }
    }

    private function storeAttachments(Request $request, int $ticketId): array
    {
        $paths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = app(\App\Services\StorageService::class)->store($file, "support/tickets/{$ticketId}");
                $paths[] = storage_url($path);
            }
        }
        return $paths;
    }

    private function formatTicket(SupportTicket $ticket, bool $withMessages = false): array
    {
        $data = [
            'id'               => $ticket->id,
            'ticket_number'    => $ticket->ticket_number,
            'subject'          => $ticket->subject,
            'description'      => $ticket->description,
            'status'           => $ticket->status,
            'priority'         => $ticket->priority,
            'firebase_chat_id' => $ticket->firebase_chat_id,
            'category'         => $ticket->category ? [
                'id'   => $ticket->category->id,
                'name' => $ticket->category->name,
                'icon' => $ticket->category->icon,
            ] : null,
            'assigned_agent' => $ticket->assignedAgent ? [
                'id'     => $ticket->assignedAgent->id,
                'name'   => $ticket->assignedAgent->name,
                'avatar' => $ticket->assignedAgent->avatar
                    ? storage_url($ticket->assignedAgent->avatar)
                    : null,
            ] : null,
            'latest_message' => $ticket->latestMessage ? $this->formatMessage($ticket->latestMessage) : null,
            'created_at'     => $ticket->created_at?->toISOString(),
            'updated_at'     => $ticket->updated_at?->toISOString(),
            'resolved_at'    => $ticket->resolved_at?->toISOString(),
            'closed_at'      => $ticket->closed_at?->toISOString(),
        ];

        if ($withMessages) {
            $data['messages'] = $ticket->messages->map(fn($m) => $this->formatMessage($m))->values();
        }

        return $data;
    }

    private function formatMessage(SupportMessage $message): array
    {
        return [
            'id'          => $message->id,
            'message'     => $message->message,
            'attachments' => $message->attachments ?? [],
            'is_admin'    => $message->is_admin,
            'read_at'     => $message->read_at?->toISOString(),
            'created_at'  => $message->created_at?->toISOString(),
            'user'        => $message->user ? [
                'id'     => $message->user->id,
                'name'   => $message->user->name,
                'avatar' => $message->user->avatar ? storage_url($message->user->avatar) : null,
            ] : null,
        ];
    }
}
