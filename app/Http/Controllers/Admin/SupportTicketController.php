<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SupportCategory;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user:id,name,email,avatar', 'category:id,name,icon', 'assignedAgent:id,name'])
            ->orderByDesc('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('ticket_number', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%"));
            });
        }

        $tickets    = $query->paginate(25);
        $categories = SupportCategory::active()->get();
        $agents     = User::where('role', 'admin')->orWhere('role', 'staff')->get(['id', 'name']);
        $stats      = [
            'open'         => SupportTicket::where('status', 'open')->count(),
            'in_progress'  => SupportTicket::where('status', 'in_progress')->count(),
            'waiting_user' => SupportTicket::where('status', 'waiting_user')->count(),
            'resolved'     => SupportTicket::where('status', 'resolved')->count(),
            'closed'       => SupportTicket::where('status', 'closed')->count(),
        ];

        return view('admin.support.index', compact('tickets', 'categories', 'agents', 'stats'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user:id,name,email,avatar,phone', 'category', 'messages.user:id,name,avatar', 'assignedAgent:id,name']);
        $agents = User::where('role', 'admin')->orWhere('role', 'staff')->get(['id', 'name']);

        return view('admin.support.show', compact('ticket', 'agents'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'status'  => 'nullable|in:open,in_progress,waiting_user,resolved,closed',
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'message'   => $request->message,
            'is_admin'  => true,
        ]);

        $updates = ['updated_at' => now()];
        if ($request->filled('status')) {
            $updates['status'] = $request->status;
            if ($request->status === 'resolved') $updates['resolved_at'] = now();
            if ($request->status === 'closed')   $updates['closed_at']   = now();
        } else {
            $updates['status'] = 'waiting_user';
        }

        $ticket->update($updates);

        return back()->with('success', 'Reply sent.');
    }

    public function assign(Request $request, SupportTicket $ticket)
    {
        $request->validate(['agent_id' => 'required|exists:users,id']);
        $ticket->update(['assigned_to' => $request->agent_id, 'status' => 'in_progress']);
        return back()->with('success', 'Ticket assigned.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate(['status' => 'required|in:open,in_progress,waiting_user,resolved,closed']);
        $updates = ['status' => $request->status];
        if ($request->status === 'resolved') $updates['resolved_at'] = now();
        if ($request->status === 'closed')   $updates['closed_at']   = now();
        $ticket->update($updates);
        return back()->with('success', 'Status updated.');
    }

    // Returns Firebase chat ID for admin live chat
    public function chatInit(SupportTicket $ticket)
    {
        $admin = Auth::user();
        return response()->json([
            'success' => true,
            'data'    => [
                'ticket_id'        => $ticket->id,
                'ticket_number'    => $ticket->ticket_number,
                'firebase_chat_id' => $ticket->firebase_chat_id,
                'subject'          => $ticket->subject,
                'status'           => $ticket->status,
                'admin' => [
                    'id'     => $admin->id,
                    'name'   => $admin->name,
                    'avatar' => $admin->avatar ? storage_url($admin->avatar) : null,
                ],
                'user' => [
                    'id'     => $ticket->user->id,
                    'name'   => $ticket->user->name,
                    'avatar' => $ticket->user->avatar ? storage_url($ticket->user->avatar) : null,
                ],
            ],
        ]);
    }

    /**
     * Proxy: POST a message to Firebase Realtime Database via REST API.
     * Also persists to support_messages so the message is never lost
     * and appears in the REST messages tab.
     */
    public function chatSend(Request $request, SupportTicket $ticket)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $chatId      = $ticket->firebase_chat_id;
        $databaseUrl = Setting::where('key', 'firebase_database_url')->value('value');

        if (!$chatId || !$databaseUrl) {
            return response()->json(['success' => false, 'message' => 'Firebase not configured.'], 422);
        }

        $admin = Auth::user();

        // ── 1. Persist to DB first (source of truth) ─────────────────────────
        $dbMessage = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $admin->id,
            'message'   => $request->message,
            'is_admin'  => true,
        ]);

        // Update ticket status to waiting_user after admin replies
        if ($ticket->status === 'in_progress' || $ticket->status === 'open') {
            $ticket->update(['status' => 'waiting_user', 'updated_at' => now()]);
        } else {
            $ticket->touch();
        }

        // ── 2. Push to Firebase for real-time delivery ────────────────────────
        try {
            $token = $this->getFirebaseAccessToken();
            $url   = rtrim($databaseUrl, '/') . "/chats/support_chats/{$chatId}/messages.json";

            $payload = [
                'sender_id'    => $admin->id,
                'sender_name'  => $admin->name,
                'message'      => $request->message,
                'message_type' => 'text',
                'timestamp'    => ['.sv' => 'timestamp'],
                'is_read'      => false,
                'db_message_id'=> $dbMessage->id, // link back to DB record
            ];

            Http::withToken($token)->post($url, $payload);
            // Firebase failure is non-fatal — message is already in DB
        } catch (\Exception $e) {
            Log::warning('Firebase push failed for support message', [
                'ticket_id'  => $ticket->id,
                'message_id' => $dbMessage->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true, 'message_id' => $dbMessage->id]);
    }

    /**
     * Get a short-lived OAuth 2.0 access token using the stored service account.
     */
    private function getFirebaseAccessToken(): string
    {
        $serviceAccountJson = Setting::where('key', 'firebase_service_account')->value('value');

        if (!$serviceAccountJson) {
            throw new \RuntimeException('Firebase service account not configured. Go to Settings → Mobile App → Firebase.');
        }

        $serviceAccount = is_array($serviceAccountJson)
            ? $serviceAccountJson
            : json_decode($serviceAccountJson, true);

        if (!$serviceAccount || empty($serviceAccount['private_key']) || empty($serviceAccount['client_email'])) {
            throw new \RuntimeException('Firebase service account JSON is invalid or missing required fields.');
        }

        try {
            $credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/firebase', 'https://www.googleapis.com/auth/cloud-platform'],
                $serviceAccount
            );

            $token = $credentials->fetchAuthToken();
        } catch (\Exception $e) {
            throw new \RuntimeException('Firebase authentication failed: ' . $e->getMessage());
        }

        if (empty($token['access_token'])) {
            throw new \RuntimeException('Firebase returned an empty access token. Check service account permissions.');
        }

        return $token['access_token'];
    }
}
