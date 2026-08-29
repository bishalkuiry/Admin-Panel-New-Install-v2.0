<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;

class CleanupDeletedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:cleanup-deleted-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete soft-deleted users whose grace period has expired.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $gracePeriod = (int) Setting::get('auth_account_deletion_grace_period', 7);
        $expiryDate = Carbon::now()->subDays($gracePeriod);

        $count = User::onlyTrashed()
            ->where('deleted_at', '<=', $expiryDate)
            ->forceDelete();

        if ($count === 0) {
            $this->info('No accounts to permanently delete.');
            return;
        }

        $this->info("Successfully deleted {$count} accounts permanently.");
    }
}
