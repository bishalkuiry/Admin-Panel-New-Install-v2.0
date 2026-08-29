<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SchedulerJob;

class SchedulerJobController extends Controller
{
    public function index()
    {
        $jobs = SchedulerJob::latest()->get();
        return view('admin.settings.cron_jobs.index', compact('jobs'));
    }

    public function create()
    {
        return view('admin.settings.cron_jobs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:url,command',
            'target' => 'required|string',
            'frequency' => 'required|string',
            'is_active' => 'boolean',
        ]);

        SchedulerJob::create($request->all());

        return redirect()->route('admin.scheduler-jobs.index')
            ->with('success', 'Cron job created successfully.');
    }

    public function edit(SchedulerJob $schedulerJob)
    {
        return view('admin.settings.cron_jobs.edit', compact('schedulerJob'));
    }

    public function update(Request $request, SchedulerJob $schedulerJob)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:url,command',
            'target' => 'required|string',
            'frequency' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $schedulerJob->update($request->all());

        return redirect()->route('admin.scheduler-jobs.index')
            ->with('success', 'Cron job updated successfully.');
    }

    public function destroy(SchedulerJob $schedulerJob)
    {
        $schedulerJob->delete();
        return back()->with('success', 'Cron job deleted successfully.');
    }

    public function toggleStatus(SchedulerJob $schedulerJob)
    {
        $schedulerJob->update(['is_active' => !$schedulerJob->is_active]);
        return back()->with('success', 'Status updated successfully.');
    }

    /**
     * Run a job manually
     */
    public function runJob(SchedulerJob $schedulerJob)
    {
        $start = microtime(true);
        try {
            if ($schedulerJob->type === 'command') {
                $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput();
                $exitCode = \Illuminate\Support\Facades\Artisan::call($schedulerJob->target, $schedulerJob->parameters ?? [], $outputBuffer);
                $output = $outputBuffer->fetch();
                $duration = round(microtime(true) - $start, 2);

                $schedulerJob->update([
                    'last_run_at' => now(),
                    'last_run_status' => $exitCode === 0 ? 'success' : 'failed',
                    'last_error' => $exitCode === 0 
                        ? "Manually Run. Output: " . \Illuminate\Support\Str::limit($output, 500) 
                        : "Manually Run. Exit Code: {$exitCode}. Output: " . \Illuminate\Support\Str::limit($output, 500)
                ]);
            } else {
                $response = \Illuminate\Support\Facades\Http::timeout(30)->get($schedulerJob->target);
                $duration = round(microtime(true) - $start, 2);
                
                $schedulerJob->update([
                    'last_run_at' => now(),
                    'last_run_status' => $response->successful() ? 'success' : 'failed',
                    'last_error' => $response->successful() 
                        ? "Manually Run. Status: {$response->status()} ({$duration}s)" 
                        : "Manually Run. Failed: {$response->status()} ({$duration}s). Body: " . \Illuminate\Support\Str::limit($response->body(), 200)
                ]);
            }

            return back()->with('success', "Job '{$schedulerJob->name}' executed successfully.");
        } catch (\Exception $e) {
            $schedulerJob->update([
                'last_run_at' => now(),
                'last_run_status' => 'failed',
                'last_error' => "Manual Run Exception: " . $e->getMessage()
            ]);
            return back()->with('error', "Job execution failed: " . $e->getMessage());
        }
    }
}
