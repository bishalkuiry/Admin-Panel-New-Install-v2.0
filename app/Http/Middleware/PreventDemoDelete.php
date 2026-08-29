<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\DemoHelper;

class PreventDemoDelete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (DemoHelper::isDemoMode()) {
            if ($this->isDeleteRequest($request)) {
                $message = 'Action Restricted: Deletion is disabled in Demo Mode.';

                if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'status'  => false,
                        'message' => $message,
                    ], 403);
                }

                return redirect()->back()->with('error', $message);
            }
        }

        return $next($request);
    }

    /**
     * Determine if the incoming HTTP request is attempting a deletion action.
     */
    protected function isDeleteRequest(Request $request): bool
    {
        // 1. Direct HTTP DELETE method or _method=DELETE parameter
        if ($request->isMethod('DELETE') || strtoupper($request->input('_method', '')) === 'DELETE') {
            return true;
        }

        $uri = strtolower($request->getRequestUri());
        $actionName = strtolower(optional($request->route())->getActionMethod() ?? '');

        // 2. Direct URI patterns matching delete / destroy / bulk-delete / remove endpoints
        $deleteKeywords = [
            '/delete',
            '/destroy',
            '/bulk-delete',
            '/remove',
            '/delete-image',
            '/unassign-delivery-partner',
            '/clean',
        ];

        foreach ($deleteKeywords as $keyword) {
            if (str_contains($uri, $keyword)) {
                return true;
            }
        }

        // 3. Controller method action names that perform deletion
        $deleteMethods = [
            'destroy',
            'delete',
            'destroyplan',
            'destroyfield',
            'destroyrule',
            'destroytab',
            'destroycard',
            'deleteimage',
            'deleteaccount',
            'removeitem',
            'removecoupon',
            'unregisterfcmtoken',
            'clean',
        ];

        if (in_array($actionName, $deleteMethods, true)) {
            return true;
        }

        // 4. Form inputs indicating delete action
        $inputAction = strtolower((string) $request->input('action', ''));
        if ($inputAction === 'delete' || $inputAction === 'destroy' || $inputAction === 'bulk_delete') {
            return true;
        }

        return false;
    }
}
