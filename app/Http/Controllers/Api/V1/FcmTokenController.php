<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FcmTokenController extends Controller
{
    public function __construct(
        private FirebaseCloudMessagingService $fcmService
    ) {}

    /**
     * Save or update FCM token for the authenticated user
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = $request->user();
        $user->fcm_token = $request->token;
        $user->save();

        Log::info("FCM token saved for user {$user->id}: " . substr($request->token, 0, 10) . '...');

        // Subscribe to user-specific topic
        $this->fcmService->subscribeToTopic($request->token, "user_{$user->id}");
        
        // Subscribe to general topics based on user role
        if ($user->role === UserRole::ADMIN || $user->role === UserRole::SUPER_ADMIN) {
            $this->fcmService->subscribeToTopic($request->token, 'admin');
        } elseif ($user->role === UserRole::STORE_OWNER || $user->role === UserRole::STORE_MANAGER || $user->role === UserRole::STORE_STAFF) {
            $this->fcmService->subscribeToTopic($request->token, 'stores');
            $this->fcmService->subscribeToTopic($request->token, 'admin'); // Store owners often get admin-level alerts
        } elseif ($user->role === UserRole::DELIVERY_PARTNER) {
            $this->fcmService->subscribeToTopic($request->token, 'drivers');
        }
        
        // Subscribe to promotions topic
        $this->fcmService->subscribeToTopic($request->token, 'promotions');

        return response()->json([
            'success' => true,
            'message' => 'FCM token saved successfully',
        ]);
    }

    /**
     * Delete FCM token (on logout)
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        
        if ($user->fcm_token) {
            // Unsubscribe from topics
            $this->fcmService->unsubscribeFromTopic($user->fcm_token, "user_{$user->id}");
            $this->fcmService->unsubscribeFromTopic($user->fcm_token, 'promotions');
            
            if ($user->role === \App\Enums\UserRole::ADMIN || $user->role === \App\Enums\UserRole::SUPER_ADMIN) {
                $this->fcmService->unsubscribeFromTopic($user->fcm_token, 'admin');
            } elseif ($user->role === \App\Enums\UserRole::STORE_OWNER || $user->role === \App\Enums\UserRole::STORE_MANAGER || $user->role === \App\Enums\UserRole::STORE_STAFF) {
                $this->fcmService->unsubscribeFromTopic($user->fcm_token, 'stores');
                $this->fcmService->unsubscribeFromTopic($user->fcm_token, 'admin');
            } elseif ($user->role === \App\Enums\UserRole::DELIVERY_PARTNER) {
                $this->fcmService->unsubscribeFromTopic($user->fcm_token, 'drivers');
            }
            
            $user->fcm_token = null;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'FCM token removed successfully',
        ]);
    }

    /**
     * Test notification
     */
    public function test(Request $request)
    {
        $user = $request->user();
        
        if (!$user->fcm_token) {
            return response()->json([
                'success' => false,
                'message' => 'No FCM token found for user',
            ], 400);
        }

        $sent = $this->fcmService->sendToUser($user, [
            'title' => 'Test Notification',
            'body' => 'This is a test notification from ' . config('app.name', 'InAllCart'),
        ], [
            'type' => 'test',
        ]);

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'Test notification sent' : 'Failed to send notification',
        ]);
    }

    /**
     * Test notification from admin panel
     */
    public function adminTest(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
        ]);

        // If user_id provided, send to that user, otherwise send to first user with FCM token
        $user = $request->user_id 
            ? User::find($request->user_id)
            : User::whereNotNull('fcm_token')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No users with FCM tokens found',
            ], 400);
        }

        if (!$user->fcm_token) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have an FCM token',
            ], 400);
        }

        $sent = $this->fcmService->sendToUser($user, [
            'title' => 'Test Notification',
            'body' => 'This is a test notification from ' . config('app.name', 'InAllCart') . ' Admin Panel',
        ], [
            'type' => 'test',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ]);

        return response()->json([
            'success' => $sent,
            'message' => $sent 
                ? "Test notification sent to {$user->name} ({$user->email})" 
                : 'Failed to send notification. Check Firebase configuration.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
