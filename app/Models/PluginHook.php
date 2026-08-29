<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class PluginHook extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'plugin_id',
        'hook_name',
        'callback',
        'priority',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the plugin that owns the hook.
     */
    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }

    /**
     * Scope a query to only include active hooks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by hook name.
     */
    public function scopeForHook($query, string $hookName)
    {
        return $query->where('hook_name', $hookName);
    }

    /**
     * Scope a query to order by priority.
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Execute the hook callback.
     */
    public function execute(...$args)
    {
        if (!$this->is_active || !$this->plugin->isActive()) {
            return null;
        }

        try {
            [$class, $method] = explode('@', $this->callback);
            
            $fullClass = $this->plugin->namespace . '\\' . $class;
            
            if (!class_exists($fullClass)) {
                Log::error("Plugin hook class not found: {$fullClass}");
                return null;
            }

            $instance = app($fullClass);
            
            if (!method_exists($instance, $method)) {
                Log::error("Plugin hook method not found: {$fullClass}@{$method}");
                return null;
            }

            return $instance->$method(...$args);
        } catch (\Exception $e) {
            Log::error("Plugin hook execution failed: {$this->callback}", [
                'plugin' => $this->plugin->name,
                'hook' => $this->hook_name,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
