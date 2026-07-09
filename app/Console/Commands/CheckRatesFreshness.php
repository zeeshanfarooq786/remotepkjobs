<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckRatesFreshness extends Command
{
    protected $signature = 'rates:check-freshness';

    protected $description = 'Warn if exchange rates have not been updated today';

    public function handle(ExchangeRateService $exchangeRates): int
    {
        if ($exchangeRates->hasRatesFromToday()) {
            $this->info('Exchange rates are fresh for today.');

            return self::SUCCESS;
        }

        $message = 'Exchange rates are stale: no USD rate row recorded today. Run rate_updater.py or exchange-rates:fetch.';
        Log::warning($message);
        $this->warn($message);

        return self::FAILURE;
    }
}
