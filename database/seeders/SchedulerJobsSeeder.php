<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchedulerJobsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobs = [
            [
                'name' => 'Process Queue Jobs',
                'type' => 'command',
                'target' => 'queue:work --stop-when-empty --tries=3 --timeout=60',
                'frequency' => 'everyMinute',
                'description' => 'Processes pending background jobs (emails, notifications, etc). Critical for system function.',
                'is_active' => true,
            ],
            [
                'name' => 'Clear Application Cache',
                'type' => 'command',
                'target' => 'cache:clear',
                'frequency' => 'daily', // Daily at midnight
                'description' => 'Clears the application cache to ensure fresh data is served.',
                'is_active' => true,
            ],
            [
                'name' => 'Update Currency Rates',
                'type' => 'command',
                'target' => 'currency:update-rates',
                'frequency' => 'daily',
                'description' => 'Fetches latest exchange rates from external API providers.',
                'is_active' => true,
            ],
            [
                'name' => 'Prune Failed Jobs',
                'type' => 'command',
                'target' => 'queue:prune-failed --hours=48',
                'frequency' => 'daily',
                'description' => 'Remove failed queue jobs older than 48 hours to keep database clean.',
                'is_active' => true,
            ],
             [
                'name' => 'Model Pruning',
                'type' => 'command',
                'target' => 'model:prune',
                'frequency' => 'daily',
                'description' => 'Prune old model records (like old notifications or logs set to be prunable).',
                'is_active' => true,
            ],
            [
                'name' => 'Process Pending Orders',
                'type' => 'command',
                'target' => 'orders:process-pending',
                'frequency' => 'everyMinute',
                'description' => 'Auto-cancels or auto-accepts orders after timeout.',
                'is_active' => true,
            ],
        ];

        foreach ($jobs as $job) {
            \App\Models\SchedulerJob::updateOrCreate(
                ['name' => $job['name']], // Unique key
                $job
            );
        }
    }
}
