<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExchangeRateService
{
    public const SUPPORTED_CURRENCIES = [
        'PKR', 'EUR', 'GBP', 'CAD', 'AUD', 'AED', 'SAR', 'BDT', 'INR', 'NGN',
    ];

    public function bulkUpsert(array $rates, ?string $recordedAt = null): int
    {
        $timestamp = $recordedAt
            ? Carbon::parse($recordedAt)
            : now();

        $updated = 0;

        DB::transaction(function () use ($rates, $timestamp, &$updated) {
            foreach ($rates as $row) {
                ExchangeRate::updateOrCreate(
                    [
                        'from_currency' => $row['from_currency'],
                        'to_currency' => $row['to_currency'],
                    ],
                    [
                        'rate' => $row['rate'],
                        'recorded_at' => $timestamp,
                    ]
                );
                $updated++;
            }
        });

        return $updated;
    }

    public function latestUpdatedAt(): ?Carbon
    {
        $value = ExchangeRate::query()
            ->where('from_currency', 'USD')
            ->max('recorded_at');

        return $value ? Carbon::parse($value) : null;
    }

    public function hasRatesFromToday(): bool
    {
        return ExchangeRate::query()
            ->where('from_currency', 'USD')
            ->whereDate('recorded_at', today())
            ->exists();
    }
}
