<?php

namespace App\Services;

use App\Models\PluginHook;
use App\Models\PluginMetric;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HookService
{
    /**
     * Registered runtime hooks (not from database)
     */
    protected array $runtimeHooks = [];

    /**
     * Register a runtime hook
     */
    public function register(string $hookName, callable $callback, int $priority = 10): void
    {
        if (!isset($this->runtimeHooks[$hookName])) {
            $this->runtimeHooks[$hookName] = [];
        }

        $this->runtimeHooks[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority
        usort($this->runtimeHooks[$hookName], function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    /**
     * Circuit breaker: track hook failures
     */
    protected array $failureCount = [];
    protected int $failureThreshold = 3;

    /**
     * Fire a hook and execute all registered callbacks
     */
    public function fire(string $hookName, ...$args)
    {
        $result = $args[0] ?? null;

        // Execute database hooks
        $dbHooks = $this->getDatabaseHooks($hookName);
        
        foreach ($dbHooks as $hook) {
            // Circuit breaker: skip if too many failures
            if (!$this->shouldExecuteHook($hookName, $hook->plugin->id)) {
                continue;
            }

            $startTime = microtime(true);
            $success = false;

            try {
                $hookResult = $hook->execute(...$args);
                $success = true;
                
                // If hook returns a value, use it as the new result
                if ($hookResult !== null) {
                    $result = $hookResult;
                    $args[0] = $result;
                }
            } catch (\Exception $e) {
                $this->recordHookFailure($hookName, $hook->plugin->id);
                
                Log::error("Hook execution failed: {$hookName}", [
                    'plugin' => $hook->plugin->name,
                    'callback' => $hook->callback,
                    'error' => $e->getMessage(),
                ]);

                // Update plugin failure count
                $hook->plugin->increment('hook_failure_count');
                $hook->plugin->update([
                    'last_error_at' => now(),
                    'last_error_message' => $e->getMessage(),
                ]);
            } finally {
                // Record performance metrics
                $executionTime = (microtime(true) - $startTime) * 1000; // ms
                
                PluginMetric::recordExecution(
                    $hook->plugin->id,
                    $hookName,
                    round($executionTime, 2),
                    $success
                );

                // Log slow hooks
                if ($executionTime > 100) {
                    Log::warning("Slow hook detected", [
                        'hook' => $hookName,
                        'plugin' => $hook->plugin->name,
                        'execution_time' => round($executionTime, 2) . 'ms',
                    ]);
                }
            }
        }

        // Execute runtime hooks
        if (isset($this->runtimeHooks[$hookName])) {
            foreach ($this->runtimeHooks[$hookName] as $hook) {
                $startTime = microtime(true);

                try {
                    $hookResult = call_user_func_array($hook['callback'], $args);
                    
                    // If hook returns a value, use it as the new result
                    if ($hookResult !== null) {
                        $result = $hookResult;
                        $args[0] = $result;
                    }
                } catch (\Exception $e) {
                    Log::error("Runtime hook execution failed: {$hookName}", [
                        'error' => $e->getMessage(),
                    ]);
                } finally {
                    $executionTime = (microtime(true) - $startTime) * 1000;
                    
                    // Log slow runtime hooks
                    if ($executionTime > 100) {
                        Log::warning("Slow runtime hook detected", [
                            'hook' => $hookName,
                            'execution_time' => round($executionTime, 2) . 'ms',
                        ]);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Circuit breaker: check if hook should execute
     */
    protected function shouldExecuteHook(string $hookName, int $pluginId): bool
    {
        $key = "{$hookName}:{$pluginId}";
        
        if (!isset($this->failureCount[$key])) {
            return true;
        }
        
        // If failures exceed threshold, skip hook
        if ($this->failureCount[$key] >= $this->failureThreshold) {
            Log::warning("Hook circuit breaker triggered", [
                'hook' => $hookName,
                'plugin_id' => $pluginId,
                'failure_count' => $this->failureCount[$key],
            ]);
            return false;
        }
        
        return true;
    }

    /**
     * Circuit breaker: record hook failure
     */
    protected function recordHookFailure(string $hookName, int $pluginId): void
    {
        $key = "{$hookName}:{$pluginId}";
        $this->failureCount[$key] = ($this->failureCount[$key] ?? 0) + 1;
    }

    /**
     * Circuit breaker: reset failure count for a hook
     */
    public function resetCircuitBreaker(string $hookName, int $pluginId): void
    {
        $key = "{$hookName}:{$pluginId}";
        unset($this->failureCount[$key]);
    }

    /**
     * Get database hooks for a specific hook name
     */
    protected function getDatabaseHooks(string $hookName): array
    {
        $cacheKey = "hooks.{$hookName}";

        return Cache::remember($cacheKey, 3600, function () use ($hookName) {
            return PluginHook::with('plugin')
                ->active()
                ->forHook($hookName)
                ->byPriority()
                ->get()
                ->filter(function ($hook) {
                    return $hook->plugin && $hook->plugin->isActive();
                })
                ->all();
        });
    }

    /**
     * Check if a hook has any registered callbacks
     */
    public function hasHook(string $hookName): bool
    {
        $hasDbHooks = PluginHook::active()
            ->forHook($hookName)
            ->exists();

        $hasRuntimeHooks = isset($this->runtimeHooks[$hookName]) && 
                          !empty($this->runtimeHooks[$hookName]);

        return $hasDbHooks || $hasRuntimeHooks;
    }

    /**
     * Get all registered hooks for a plugin
     */
    public function getPluginHooks(int $pluginId): array
    {
        return PluginHook::where('plugin_id', $pluginId)
            ->orderBy('hook_name')
            ->orderBy('priority')
            ->get()
            ->toArray();
    }

    /**
     * Clear hook cache
     */
    public function clearCache(): void
    {
        // Clear all hook caches
        $hookNames = PluginHook::distinct()->pluck('hook_name');
        
        foreach ($hookNames as $hookName) {
            Cache::forget("hooks.{$hookName}");
        }
    }

    /**
     * Get all available hook names
     */
    public function getAvailableHooks(): array
    {
        return [
            // Application Lifecycle
            'app.boot' => 'Application booted',
            'app.ready' => 'Application ready',

            // Order Hooks
            'order.creating' => 'Before order creation',
            'order.created' => 'After order created',
            'order.updating' => 'Before order update',
            'order.updated' => 'After order updated',
            'order.status.changed' => 'Order status changed',
            'order.cancelled' => 'Order cancelled',
            'order.completed' => 'Order completed',

            // Payment Hooks
            'payment.methods' => 'Register payment methods',
            'payment.processing' => 'Before payment processing',
            'payment.processed' => 'After payment processed',
            'payment.failed' => 'Payment failed',
            'payment.refunded' => 'Payment refunded',

            // Product Hooks
            'product.created' => 'Product created',
            'product.updated' => 'Product updated',
            'product.deleted' => 'Product deleted',
            'product.stock.low' => 'Low stock alert',

            // User Hooks
            'user.registered' => 'User registered',
            'user.login' => 'User logged in',
            'user.logout' => 'User logged out',

            // Admin Hooks
            'admin.menu' => 'Register admin menu items',
            'admin.dashboard.widgets' => 'Register dashboard widgets',
            'admin.settings.tabs' => 'Register settings tabs',

            // API Hooks
            'api.routes' => 'Register API routes',
            'api.middleware' => 'Register API middleware',

            // View Hooks
            'view.header' => 'Inject into header',
            'view.footer' => 'Inject into footer',
            'view.sidebar' => 'Inject into sidebar',
        ];
    }
}
