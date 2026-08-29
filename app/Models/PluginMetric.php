<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PluginMetric extends Model
{
    protected $fillable = [
        'plugin_id',
        'hook_name',
        'execution_count',
        'total_execution_time',
        'avg_execution_time',
        'max_execution_time',
        'failure_count',
        'last_executed_at',
    ];

    protected $casts = [
        'execution_count' => 'integer',
        'total_execution_time' => 'decimal:2',
        'avg_execution_time' => 'decimal:2',
        'max_execution_time' => 'decimal:2',
        'failure_count' => 'integer',
        'last_executed_at' => 'datetime',
    ];

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }

    /**
     * Record hook execution
     */
    public static function recordExecution(
        int $pluginId,
        string $hookName,
        float $executionTime,
        bool $success = true
    ): void {
        $metric = static::firstOrCreate(
            [
                'plugin_id' => $pluginId,
                'hook_name' => $hookName,
            ],
            [
                'execution_count' => 0,
                'total_execution_time' => 0,
                'avg_execution_time' => 0,
                'max_execution_time' => 0,
                'failure_count' => 0,
            ]
        );

        $metric->increment('execution_count');
        $metric->increment('total_execution_time', $executionTime);
        
        if (!$success) {
            $metric->increment('failure_count');
        }

        // Update averages and max
        $metric->avg_execution_time = $metric->total_execution_time / $metric->execution_count;
        $metric->max_execution_time = max($metric->max_execution_time, $executionTime);
        $metric->last_executed_at = now();
        $metric->save();
    }

    /**
     * Get slow hooks (> 100ms average)
     */
    public static function getSlowHooks(int $threshold = 100): array
    {
        return static::where('avg_execution_time', '>', $threshold)
            ->with('plugin')
            ->orderBy('avg_execution_time', 'desc')
            ->get()
            ->toArray();
    }
}
