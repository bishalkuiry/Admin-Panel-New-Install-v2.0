<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulerJob extends Model
{
    protected $fillable = [
        'name',
        'type',
        'target',
        'frequency',
        'parameters',
        'description',
        'is_active',
        'last_run_at',
        'last_run_status',
        'last_error'
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    /**
     * Get frequency label for display
     */
    public function getFrequencyLabelAttribute()
    {
        $map = [
            'everyMinute' => 'Every Minute',
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];

        return $map[$this->frequency] ?? $this->frequency;
    }
}
