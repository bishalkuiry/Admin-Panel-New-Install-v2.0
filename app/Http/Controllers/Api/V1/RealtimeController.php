<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BroadcastService;
use Illuminate\Http\Request;

class RealtimeController extends Controller
{
    public function __construct(
        private BroadcastService $broadcastService
    ) {}

    /**
     * Stream events using Server-Sent Events (SSE)
     * Fallback for when Pusher/Redis not available
     */
    public function stream(Request $request)
    {
        $channels = $request->query('channels', ['global']);
        if (is_string($channels)) {
            $channels = explode(',', $channels);
        }
        
        $lastEventId = $request->header('Last-Event-ID');
        
        $this->broadcastService->streamSSE($channels, $lastEventId);
    }

    /**
     * Get recent events (polling fallback)
     */
    public function events(Request $request)
    {
        $channel = $request->query('channel', 'global');
        $since = $request->query('since');
        
        $events = $this->broadcastService->getEvents($channel, $since);
        
        return response()->json([
            'success' => true,
            'data' => $events,
            'timestamp' => now()->toISOString(),
            'driver' => $this->broadcastService->getDriverInfo()['current_driver'],
        ]);
    }

    /**
     * Get available channels
     */
    public function channels()
    {
        return response()->json([
            'success' => true,
            'data' => $this->broadcastService->getChannels(),
            'driver_info' => $this->broadcastService->getDriverInfo(),
        ]);
    }

    /**
     * Pusher authentication for private channels
     */
    public function pusherAuth(Request $request)
    {
        $request->validate([
            'socket_id' => 'required|string',
            'channel_name' => 'required|string',
        ]);

        try {
            $auth = $this->broadcastService->pusherAuth(
                $request->channel_name,
                $request->socket_id
            );

            return response()->json($auth);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
