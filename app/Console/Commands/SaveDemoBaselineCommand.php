<?php

namespace App\Console\Commands;

use App\Services\DemoResetService;
use Illuminate\Console\Command;
use Exception;

class SaveDemoBaselineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:save-baseline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Captures current database and uploaded files as pristine baseline snapshot for demo resets';

    /**
     * Execute the console command.
     */
    public function handle(DemoResetService $demoResetService): int
    {
        $this->info('Capturing pristine baseline snapshot (Database SQL + Uploaded Media)...');

        try {
            $demoResetService->saveBaseline();
            $this->info('✔ Demo baseline snapshot successfully saved!');
            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('Failed to save demo baseline snapshot: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
