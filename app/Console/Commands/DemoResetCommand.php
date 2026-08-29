<?php

namespace App\Console\Commands;

use App\Services\DemoResetService;
use Illuminate\Console\Command;
use Exception;

class DemoResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restores database and uploaded media files back to pristine demo baseline';

    /**
     * Execute the console command.
     */
    public function handle(DemoResetService $demoResetService): int
    {
        $info = $demoResetService->getBaselineInfo();

        if (!$info['is_enabled'] && !$this->option('force')) {
            $this->warn('Demo reset is currently disabled in settings.');
            return Command::SUCCESS;
        }

        $this->info('Starting Demo Baseline Data Restoration...');

        try {
            $demoResetService->restoreBaseline();
            $this->info('✔ Demo environment successfully restored to pristine baseline state!');
            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('Failed to restore demo baseline: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
