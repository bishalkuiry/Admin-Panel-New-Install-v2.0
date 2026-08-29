{{-- Enterprise Real-time Notifications Component --}}
{{-- Supports: Pusher (WebSocket), Redis, Database/SSE --}}
@php
    $broadcastConfig = \App\Models\Setting::getGroup('broadcast');
    $pusherKey = $broadcastConfig['pusher_app_key'] ?? '';
    $pusherCluster = $broadcastConfig['pusher_app_cluster'] ?? 'mt1';
    $driver = $broadcastConfig['driver'] ?? 'database';
@endphp

<div x-data="realtimeNotifications()" x-init="init()" class="relative">
    {{-- Notification Bell --}}
    <button @click="showPanel = !showPanel" class="relative p-2 text-gray-500 hover:text-gray-700 rounded-lg">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount"
              class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"></span>
        <span x-show="connected" class="absolute top-0 right-0 w-2 h-2 rounded-full animate-pulse"
              :class="driver === 'pusher' ? 'bg-green-500' : (driver === 'redis' ? 'bg-blue-500' : 'bg-yellow-500')"></span>
    </button>

    {{-- Notification Panel --}}
    <div x-show="showPanel" x-transition @click.away="showPanel = false"
         class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden">
        
        {{-- Header --}}
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-900">Notifications</span>
                <span x-show="connected" class="text-xs flex items-center gap-1 px-2 py-0.5 rounded-full"
                      :class="driver === 'pusher' ? 'bg-green-100 text-green-700' : (driver === 'redis' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700')">
                    <span class="w-1.5 h-1.5 rounded-full animate-pulse"
                          :class="driver === 'pusher' ? 'bg-green-500' : (driver === 'redis' ? 'bg-blue-500' : 'bg-yellow-500')"></span>
                    <span x-text="driver === 'pusher' ? 'WebSocket' : (driver === 'redis' ? 'Redis' : 'SSE')"></span>
                </span>
            </div>
            <button @click="clearAll()" x-show="notifications.length > 0" class="text-xs text-gray-500 hover:text-gray-700">Clear all</button>
        </div>

        {{-- Notifications List --}}
        <div class="max-h-96 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <div class="px-4 py-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-sm">No notifications yet</p>
                    <p class="text-xs text-gray-400 mt-1">Real-time updates will appear here</p>
                </div>
            </template>

            <template x-for="(notification, index) in notifications" :key="'notif-' + (notification.id || index) + '-' + index">
                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition cursor-pointer"
                     :class="{ 'bg-orange-50': !notification.read }" @click="markAsRead(notification)">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                             :class="getIconClass(notification.event)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-html="getIcon(notification.event)"></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="notification.message"></p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-400" x-text="formatTime(notification.timestamp)"></span>
                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-500" x-text="notification.driver"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="px-4 py-2 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
            <a href="{{ route('admin.settings.index') }}" class="text-xs text-gray-500 hover:text-gray-700">Configure</a>
            <a href="{{ route('admin.dashboard') }}" class="text-xs text-orange-600 hover:text-orange-700 font-medium">View all →</a>
        </div>
    </div>
</div>

{{-- Pusher JS (only if configured) --}}
@if(!empty($pusherKey))
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@endif

<script>
function realtimeNotifications() {
    return {
        notifications: [],
        unreadCount: 0,
        showPanel: false,
        connected: false,
        driver: 'database',
        pusher: null,
        eventSource: null,

        init() {
            try {
                this.loadStoredNotifications();
                this.connect();
                
                // Close connections on page navigation to prevent blocking
                window.addEventListener('beforeunload', () => this.disconnect());
                window.addEventListener('pagehide', () => this.disconnect());
            } catch (e) {
                console.error('Notification init error:', e);
                // Don't let notification errors break the page
            }
        },
        
        disconnect() {
            if (this.eventSource) {
                this.eventSource.close();
                this.eventSource = null;
            }
            if (this.pusher) {
                this.pusher.disconnect();
                this.pusher = null;
            }
            this.connected = false;
        },

        connect() {
            const pusherKey = '{{ $pusherKey }}';
            const pusherCluster = '{{ $pusherCluster }}';
            const preferredDriver = '{{ $driver }}';

            // Try Pusher first if configured
            if (pusherKey && (preferredDriver === 'auto' || preferredDriver === 'pusher')) {
                this.connectPusher(pusherKey, pusherCluster);
            } else {
                // Fallback to SSE
                this.connectSSE();
            }
        },

        connectPusher(key, cluster) {
            try {
                this.pusher = new Pusher(key, {
                    cluster: cluster,
                    forceTLS: true
                });

                const channels = ['orders', 'inventory', 'admin'];
                channels.forEach(channelName => {
                    const channel = this.pusher.subscribe(channelName);
                    
                    channel.bind_global((event, data) => {
                        if (event.startsWith('pusher:')) return;
                        this.handleEvent(event, data);
                    });
                });

                this.pusher.connection.bind('connected', () => {
                    this.connected = true;
                    this.driver = 'pusher';
                });

                this.pusher.connection.bind('error', (err) => {
                    this.connectSSE();
                });
            } catch (e) {
                this.connectSSE();
            }
        },

        connectSSE() {
            // Don't connect if already connected
            if (this.eventSource) return;
            
            const channels = ['orders', 'inventory', 'admin', 'global'];
            const url = `/api/v1/realtime/stream?channels=${channels.join(',')}`;
            
            try {
                this.eventSource = new EventSource(url);
                
                this.eventSource.onopen = () => {
                    this.connected = true;
                    this.driver = 'sse';
                };

                this.eventSource.onerror = (e) => {
                    this.connected = false;
                    // Close on error to prevent blocking
                    if (this.eventSource) {
                        this.eventSource.close();
                        this.eventSource = null;
                    }
                    // Don't retry automatically - SSE is optional
                };

                // Listen to events
                ['new-order', 'order-status', 'order-cancelled', 'low-stock', 'out-of-stock', 'notification', 'test'].forEach(event => {
                    this.eventSource.addEventListener(event, (e) => {
                        try {
                            const data = JSON.parse(e.data);
                            this.handleEvent(data.event, data);
                        } catch (err) {
                        }
                    });
                });
            } catch (e) {
                this.connected = false;
            }
        },

        handleEvent(event, data) {
            const notification = {
                id: data.id || `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                event: event,
                title: this.getTitle(event, data.data || data),
                message: this.getMessage(event, data.data || data),
                timestamp: data.timestamp || new Date().toISOString(),
                driver: data.driver || this.driver,
                data: data.data || data,
                read: false,
            };

            this.notifications.unshift(notification);
            if (this.notifications.length > 50) {
                this.notifications = this.notifications.slice(0, 50);
            }

            this.unreadCount++;
            this.saveNotifications();
            this.playSound();
            this.showBrowserNotification(notification);
        },

        getTitle(event, data) {
            const titles = {
                'new-order': '🛒 New Order',
                'order-status': '📦 Order Updated',
                'order-cancelled': '❌ Order Cancelled',
                'low-stock': '⚠️ Low Stock Alert',
                'out-of-stock': '🚫 Out of Stock',
                'notification': '📢 ' + (data.title || 'Notification'),
                'test': '🧪 Test Notification',
            };
            return titles[event] || 'Notification';
        },

        getMessage(event, data) {
            switch (event) {
                case 'new-order': 
                    return `Order ${data.order_number || '#'} - $${data.total || 0} from ${data.customer || 'Customer'}`;
                case 'order-status': 
                    return `Order ${data.order_number || '#'} → ${data.new_status || 'updated'}`;
                case 'order-cancelled': 
                    return `Order ${data.order_number || '#'} was cancelled`;
                case 'low-stock': 
                    return `${data.product_name || 'Product'} has only ${data.quantity || 0} left`;
                case 'out-of-stock': 
                    return `${data.product_name || 'Product'} is now out of stock`;
                case 'notification':
                case 'test':
                    return data.message || 'New notification';
                default: 
                    return JSON.stringify(data).substring(0, 100);
            }
        },

        getIconClass(event) {
            const classes = {
                'new-order': 'bg-green-100 text-green-600',
                'order-status': 'bg-blue-100 text-blue-600',
                'order-cancelled': 'bg-red-100 text-red-600',
                'low-stock': 'bg-yellow-100 text-yellow-600',
                'out-of-stock': 'bg-red-100 text-red-600',
                'notification': 'bg-purple-100 text-purple-600',
                'test': 'bg-gray-100 text-gray-600',
            };
            return classes[event] || 'bg-gray-100 text-gray-600';
        },

        getIcon(event) {
            const icons = {
                'new-order': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>',
                'order-status': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
                'low-stock': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            };
            return icons[event] || '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>';
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = (now - date) / 1000;
            if (diff < 60) return 'Just now';
            if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
            if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
            return date.toLocaleDateString();
        },

        markAsRead(notification) {
            if (!notification.read) {
                notification.read = true;
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                this.saveNotifications();
            }
        },

        clearAll() {
            this.notifications = [];
            this.unreadCount = 0;
            this.saveNotifications();
        },

        saveNotifications() {
            localStorage.setItem('admin_notifications', JSON.stringify(this.notifications));
            localStorage.setItem('admin_unread_count', this.unreadCount);
        },

        loadStoredNotifications() {
            try {
                const stored = localStorage.getItem('admin_notifications');
                if (stored) {
                    const parsed = JSON.parse(stored);
                    if (Array.isArray(parsed)) {
                        this.notifications = parsed.filter(n => n && typeof n === 'object');
                    }
                }
                this.unreadCount = parseInt(localStorage.getItem('admin_unread_count') || '0');
            } catch (e) {
                console.error('Failed to load notifications', e);
                this.notifications = [];
                this.unreadCount = 0;
            }
        },

        playSound() {
            // Optional: Play notification sound
        },

        showBrowserNotification(notification) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(notification.title, { body: notification.message, icon: '/favicon.ico' });
            }
        }
    };
}

if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
</script>
