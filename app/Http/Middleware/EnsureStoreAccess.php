<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreAccess
{
    /**
     * Ensure user has access to a store (for seller panel)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('admin.login');
        }

        if (!$user->role->isStoreRole() && $user->role->value !== 'store_owner' && $user->role->value !== 'super_admin') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Access denied. Seller account required.'], 403);
            }
            abort(403, 'Access denied. Seller account required.');
        }

        $store = $user->getCurrentStore();

        if (!$store && $user->role->value !== 'super_admin') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No store associated with this account.'], 403);
            }
            abort(403, 'No store associated with this account.');
        }

        // Add store to request for easy access
        $request->merge(['current_store' => $store]);

        return $next($request);
    }
}
