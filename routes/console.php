<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Setting;
use App\Models\SchedulerJob;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule currency exchange rate updates
Schedule::command('currency:update-rates')->daily()->when(function () {
    $frequency = Setting::get('exchange_rate_update_frequency', 'daily');
    $autoUpdate = Setting::get('auto_exchange_rate_update', '1');
    return $autoUpdate === '1' && $frequency === 'daily';
});

Schedule::command('currency:update-rates')->hourly()->when(function () {
    $frequency = Setting::get('exchange_rate_update_frequency', 'daily');
    $autoUpdate = Setting::get('auto_exchange_rate_update', '1');
    return $autoUpdate === '1' && $frequency === 'hourly';
});

Schedule::command('currency:update-rates')->weekly()->when(function () {
    $frequency = Setting::get('exchange_rate_update_frequency', 'daily');
    $autoUpdate = Setting::get('auto_exchange_rate_update', '1');
    return $autoUpdate === '1' && $frequency === 'weekly';
});

// Process pending orders (auto-cancel or auto-accept after timeout)
Schedule::command('orders:process-pending')->everyMinute();

// Clean up soft-deleted users after grace period
Schedule::command('auth:cleanup-deleted-users')->daily();

// Demo Environment Auto-Reset (Restores pristine DB & images every 45 minutes)
Schedule::command('demo:reset')->cron('*/45 * * * *')->when(function () {
    return filter_var(env('DEMO_MODE', config('app.demo_mode', true)), FILTER_VALIDATE_BOOLEAN)
        && \App\Models\Setting::get('demo_reset_enabled', '1') === '1';
});

// Dynamic Scheduler from Database
try {
    if (\Illuminate\Support\Facades\Schema::hasTable('scheduler_jobs')) {
        $jobs = SchedulerJob::where('is_active', true)->get();

        foreach ($jobs as $job) {
            /** @var \App\Models\SchedulerJob $job */
            if ($job->type === 'command') {
                // Enterprise-grade Command Execution
                $sched = Schedule::call(function () use ($job) {
                    // Lazy-validate command existence at runtime to avoid boot-time recursion
                    if (!Artisan::has($job->target)) {
                        \Illuminate\Support\Facades\Log::warning("Dynamic Scheduler: Skipping command '{$job->target}' as it was not found.");
                        $job->update([
                            'last_run_at' => now(),
                            'last_run_status' => 'skipped',
                            'last_error' => "Command '{$job->target}' not found."
                        ]);
                        return;
                    }

                    $start = microtime(true);
                    try {
                        // Create a buffered output to capture the command's output
                        $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput();
                        
                        // Run the command
                        $exitCode = Artisan::call($job->target, $job->parameters ?? [], $outputBuffer);
                        $output = $outputBuffer->fetch();
                        $duration = round(microtime(true) - $start, 2);

                        $job->update([
                            'last_run_at' => now(),
                            'last_run_status' => $exitCode === 0 ? 'success' : 'failed',
                            'last_error' => $exitCode === 0 
                                ? "Output: " . Str::limit($output, 500) 
                                : "Exit Code: {$exitCode}. Output: " . Str::limit($output, 500)
                        ]);
                    } catch (\Exception $e) {
                         $job->update([
                            'last_run_at' => now(),
                            'last_run_status' => 'failed',
                            'last_error' => "Exception: " . $e->getMessage()
                        ]);
                    }
                });
            } else {
                // Enterprise-grade URL Ping
                $sched = Schedule::call(function () use ($job) {
                    $start = microtime(true);
                    try {
                        $response = \Illuminate\Support\Facades\Http::timeout(30)->get($job->target);
                        $duration = round(microtime(true) - $start, 2);
                        
                        $job->update([
                            'last_run_at' => now(),
                            'last_run_status' => $response->successful() ? 'success' : 'failed',
                            'last_error' => $response->successful() 
                                ? "Status: {$response->status()} ({$duration}s)" 
                                : "Failed: {$response->status()} ({$duration}s). Body: " . Str::limit($response->body(), 200)
                        ]);
                    } catch (\Exception $e) {
                        $job->update([
                            'last_run_at' => now(),
                            'last_run_status' => 'failed',
                            'last_error' => "Connection Error: " . $e->getMessage()
                        ]);
                    }
                });
            }

            // Apply Frequency
            // e.g. ->daily(), ->hourly(), ->cron('* * * * *')
            if (method_exists($sched, $job->frequency)) {
                $sched->{$job->frequency}();
            } else {
                $sched->cron($job->frequency);
            }

            
        }
    }
} catch (\Exception $e) {
    // Ignore schema errors during migration
}
