<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class CleanExpiredJobs extends Command
{
    protected $signature = 'jobs:clean-expired';

    protected $description = 'Mark jobs inactive if posted more than 30 days ago';

    public function handle(): int
    {
        $count = Job::query()
            ->where('is_active', true)
            ->where('posted_at', '<', now()->subDays(30))
            ->update(['is_active' => false]);

        $this->info("Marked {$count} job(s) as inactive.");

        return self::SUCCESS;
    }
}
