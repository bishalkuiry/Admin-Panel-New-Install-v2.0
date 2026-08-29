<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log to external service (Sentry, Bugsnag, etc.)
        });

        // API Exception handling — also covers AJAX requests from admin panel
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->wantsJson() || $request->ajax()) {
                return $this->handleApiException($e);
            }
        });
    }

    private function handleApiException(Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], 404);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Handle HTTP exceptions (abort(403), abort(404), etc.) with their proper status codes
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: match($status) {
                403 => 'Access denied',
                404 => 'Not found',
                405 => 'Method not allowed',
                429 => 'Too many requests',
                default => 'HTTP error',
            };
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        // Log the error
        report($e);

        return response()->json([
            'success' => false,
            'message' => config('app.debug') ? $e->getMessage() : 'Server error',
        ], 500);
    }
}
