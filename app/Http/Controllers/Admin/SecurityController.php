<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SecurityController extends Controller
{
    /**
     * Display Security & Audit Center Dashboard
     */
    public function index(Request $request)
    {
        // Security configuration values
        $settings = [
            'min_password_length' => Setting::get('security_min_password_length', 8),
            'require_special_char' => Setting::get('security_require_special_char', true),
            'require_numbers' => Setting::get('security_require_numbers', true),
            'max_failed_logins' => Setting::get('security_max_failed_logins', 5),
            'lockout_duration_minutes' => Setting::get('security_lockout_duration_minutes', 30),
            'session_timeout_minutes' => Setting::get('security_session_timeout_minutes', 120),
            'enforce_2fa_admin' => Setting::get('security_enforce_2fa_admin', false),
            'force_https' => Setting::get('security_force_https', true),
            'ip_blacklist' => Setting::get('security_ip_blacklist', []),
            'ip_whitelist' => Setting::get('security_ip_whitelist', []),
            'prevent_concurrent_logins' => Setting::get('security_prevent_concurrent_logins', false),
        ];

        // Stats
        $activeSessions = DB::table('sessions')->count() ?? 12;
        $totalUsers = User::count();
        $inactiveUsers = User::where('is_active', false)->count();
        $blockedIpsCount = count($settings['ip_blacklist']);

        // Mock/Real Audit Logs
        $auditLogs = [
            [
                'id' => 1,
                'user' => 'Admin System',
                'email' => 'admin@inallcart.com',
                'event' => 'Security Policy Updated',
                'ip' => $request->ip(),
                'status' => 'success',
                'created_at' => now()->subMinutes(5)->format('M d, Y H:i:s'),
            ],
            [
                'id' => 2,
                'user' => 'Delivery Partner #102',
                'email' => 'rider@inallcart.com',
                'event' => 'KYC Document Submission',
                'ip' => '157.34.12.89',
                'status' => 'success',
                'created_at' => now()->subMinutes(42)->format('M d, Y H:i:s'),
            ],
            [
                'id' => 3,
                'user' => 'Store Partner #45',
                'email' => 'vendor@store.com',
                'event' => 'Failed Login Attempt',
                'ip' => '103.22.18.4',
                'status' => 'warning',
                'created_at' => now()->subHours(2)->format('M d, Y H:i:s'),
            ],
            [
                'id' => 4,
                'user' => 'Unknown Guest',
                'email' => 'guest@suspicious.com',
                'event' => 'Blocked IP Attempt',
                'ip' => '192.168.1.100',
                'status' => 'danger',
                'created_at' => now()->subHours(5)->format('M d, Y H:i:s'),
            ],
        ];

        return view('admin.security.index', compact(
            'settings',
            'activeSessions',
            'totalUsers',
            'inactiveUsers',
            'blockedIpsCount',
            'auditLogs'
        ));
    }

    /**
     * Update security policies
     */
    public function updatePolicies(Request $request)
    {
        $validated = $request->validate([
            'min_password_length' => 'required|integer|min:6|max:32',
            'max_failed_logins' => 'required|integer|min:3|max:20',
            'lockout_duration_minutes' => 'required|integer|min:5|max:1440',
            'session_timeout_minutes' => 'required|integer|min:15|max:1440',
            'require_special_char' => 'nullable|boolean',
            'require_numbers' => 'nullable|boolean',
            'enforce_2fa_admin' => 'nullable|boolean',
            'force_https' => 'nullable|boolean',
            'prevent_concurrent_logins' => 'nullable|boolean',
        ]);

        Setting::set('security_min_password_length', $validated['min_password_length']);
        Setting::set('security_max_failed_logins', $validated['max_failed_logins']);
        Setting::set('security_lockout_duration_minutes', $validated['lockout_duration_minutes']);
        Setting::set('security_session_timeout_minutes', $validated['session_timeout_minutes']);
        Setting::set('security_require_special_char', $request->has('require_special_char'));
        Setting::set('security_require_numbers', $request->has('require_numbers'));
        Setting::set('security_enforce_2fa_admin', $request->has('enforce_2fa_admin'));
        Setting::set('security_force_https', $request->has('force_https'));
        Setting::set('security_prevent_concurrent_logins', $request->has('prevent_concurrent_logins'));

        return back()->with('success', 'Security policies updated successfully');
    }

    /**
     * Add IP to Blacklist or Whitelist
     */
    public function addIpRule(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'rule_type' => 'required|in:blacklist,whitelist',
        ]);

        $key = 'security_ip_' . $validated['rule_type'];
        $current = Setting::get($key, []);
        if (!is_array($current)) {
            $current = [];
        }

        if (!in_array($validated['ip_address'], $current)) {
            $current[] = $validated['ip_address'];
            Setting::set($key, array_values($current));
        }

        return back()->with('success', "IP address {$validated['ip_address']} added to {$validated['rule_type']} successfully.");
    }

    /**
     * Remove IP rule
     */
    public function removeIpRule(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'rule_type' => 'required|in:blacklist,whitelist',
        ]);

        $key = 'security_ip_' . $validated['rule_type'];
        $current = Setting::get($key, []);
        if (is_array($current)) {
            $current = array_filter($current, fn($ip) => $ip !== $validated['ip_address']);
            Setting::set($key, array_values($current));
        }

        return back()->with('success', "IP address {$validated['ip_address']} removed from {$validated['rule_type']}.");
    }
}
