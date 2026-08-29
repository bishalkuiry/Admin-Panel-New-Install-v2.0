<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\UserRole;
use App\Support\DynamicRole;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Check if user has required role
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        // Super admin has access to everything
        $userRoleSlug = $this->resolveRoleValue($user->role);
        if (in_array($userRoleSlug, ['super_admin', 'admin', UserRole::SUPER_ADMIN->value], true)) {
            return $next($request);
        }

        // Check if user has any of the required roles or permissions
        foreach ($roles as $roleOrPermission) {
            // If argument contains a dot, treat it as a permission
            if (str_contains($roleOrPermission, '.')) {
                if ($user->hasPermission($roleOrPermission)) {
                    return $next($request);
                }
            }
            // Otherwise treat it as a role slug — safe extraction handles
            // UserRole Enum, DynamicRole object, and raw-string fallback
            else {
                $userRoleValue = $this->resolveRoleValue($user->role);
                if ($userRoleValue === $roleOrPermission) {
                    return $next($request);
                }
            }
        }

        // Log the denial so we can diagnose permission issues
        Log::warning('CheckRole: access denied', [
            'user_id'        => $user->id,
            'user_email'     => $user->email,
            'user_role'      => $this->resolveRoleValue($user->role),
            'required'       => $roles,
            'url'            => $request->fullUrl(),
            'method'         => $request->method(),
            'expects_json'   => $request->expectsJson(),
            'wants_json'     => $request->wantsJson(),
            'accept_header'  => $request->header('Accept'),
            'x_requested'    => $request->header('X-Requested-With'),
            'user_permissions' => $user->permissions ?? [],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Required role: ' . implode(' or ', $roles),
            ], 403);
        }

        // Always return JSON for non-GET requests (AJAX mutations) even without Accept header
        if (!in_array($request->method(), ['GET', 'HEAD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Required role: ' . implode(' or ', $roles),
            ], 403);
        }

        abort(403, 'Access denied');
    }

    /**
     * Safely extract the slug/value from any role representation.
     * Handles:
     *   - UserRole Enum instance  → $role->value
     *   - DynamicRole object      → $role->value
     *   - Raw string (fallback)   → $role  (returned as-is)
     */
    private function resolveRoleValue(mixed $role): string
    {
        if ($role instanceof UserRole || $role instanceof DynamicRole) {
            return $role->value;
        }

        // Raw-string fallback — should not normally occur but is now safe
        return (string) $role;
    }
}
