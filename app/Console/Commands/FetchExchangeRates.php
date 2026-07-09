<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchExchangeRates extends Command
{
    protected $signature = 'exchange-rates:fetch';

    protected $description = 'Fetch live USD exchange rates from open.er-api.com and store in database';

    public function handle(ExchangeRateService $exchangeRates): int
    {
        $apiUrl = config('services.exchange_rate.url');
        $response = Http::timeout(30)->get($apiUrl);

        if (! $response->successful()) {
            $this->error('Failed to fetch exchange rates: HTTP '.$response->status());

            return self::FAILURE;
        }

        $payload = $response->json();

        if (($payload['result'] ?? '') !== 'success') {
            $this->error('Exchange API returned an error.');

            return self::FAILURE;
        }

        $apiRates = $payload['rates'] ?? [];
        $rows = [];

        foreach (ExchangeRateService::SUPPORTED_CURRENCIES as $currency) {
            if (! isset($apiRates[$currency])) {
                $this->warn("Skipping missing currency: {$currency}");
                continue;
            }

            $rows[] = [
                'from_currency' => 'USD',
                'to_currency' => $currency,
                'rate' => (float) $apiRates[$currency],
            ];
        }

        if ($rows === []) {
            $this->error('No supported currencies returned from API.');

            return self::FAILURE;
        }

        $count = $exchangeRates->bulkUpsert($rows);

        $this->info("Updated {$count} exchange rates.");
        $this->line('Last updated: '.$exchangeRates->latestUpdatedAt()?->toDateTimeString());

        return self::SUCCESS;
    }
}
