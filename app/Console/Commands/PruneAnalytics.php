<?php

namespace App\Console\Commands;

use App\Models\Visit;
use Illuminate\Console\Command;

class PruneAnalytics extends Command
{
    protected $signature = 'analytics:prune {--days=90 : Delete visits older than this}';

    protected $description = 'Delete analytics data past the retention window';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        // Events cascade on delete, so removing the visit clears both tables.
        $deleted = Visit::where('started_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} visits older than {$days} days.");

        return self::SUCCESS;
    }
}
