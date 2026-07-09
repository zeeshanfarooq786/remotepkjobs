<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\PlatformRate;
use Carbon\Carbon;

class CalculatorService
{
    private const PLATFORM_MAP = [
        'upwork' => 'Upwork',
        'fiverr' => 'Fiverr',
        'toptal' => 'Toptal',
    ];

    private const PROCESSOR_MAP = [
        'payoneer' => 'Payoneer',
        'wise' => 'Wise',
        'direct' => 'HBL_PKR',
    ];

    public function getRatesForFrontend(): array
    {
        $platformRows = PlatformRate::query()->get();
        $exchangeRows = ExchangeRate::query()->get();

        $platforms = [
            'upwork' => ['fee_percent' => 10.0, 'fee_type' => 'flat_percent'],
            'fiverr' => ['fee_percent' => 20.0, 'fee_type' => 'flat_percent'],
            'toptal' => ['fee_percent' => 0.0, 'fee_type' => 'flat_percent'],
        ];

        foreach ($platformRows as $row) {
            $key = array_search($row->platform, self::PLATFORM_MAP, true);
            if ($key !== false && $row->fee_type === 'flat_percent') {
                $platforms[$key]['fee_percent'] = (float) $row->fee_value;
            }
        }

        $processors = [
            'payoneer' => ['fee_type' => 'percent', 'fee_percent' => 2.0, 'fee_flat_usd' => 0.0],
            'wise' => ['fee_type' => 'percent', 'fee_percent' => 0.5, 'fee_flat_usd' => 0.0],
            'direct' => ['fee_type' => 'flat', 'fee_percent' => 0.0, 'fee_flat_usd' => 1.0],
        ];

        foreach ($platformRows as $row) {
            if ($row->platform === 'Payoneer' && $row->fee_type === 'withdrawal_percent') {
                $processors['payoneer']['fee_percent'] = (float) $row->fee_value;
            }
            if ($row->platform === 'Wise' && $row->fee_type === 'transfer_percent') {
                $processors['wise']['fee_percent'] = (float) $row->fee_value;
            }
            if ($row->platform === 'HBL_PKR' && $row->fee_type === 'bank_withdrawal_flat') {
                $processors['direct']['fee_flat_usd'] = (float) $row->fee_value;
            }
        }

        $usdToPkr = 278.0;
        $usdToEur = 0.92;
        $usdToGbp = 0.79;

        foreach ($exchangeRows as $row) {
            if ($row->from_currency === 'USD' && $row->to_currency === 'PKR') {
                $usdToPkr = (float) $row->rate;
            }
            if ($row->from_currency === 'USD' && $row->to_currency === 'EUR') {
                $usdToEur = (float) $row->rate;
            }
            if ($row->from_currency === 'USD' && $row->to_currency === 'GBP') {
                $usdToGbp = (float) $row->rate;
            }
        }

        $latestRecordedAt = ExchangeRate::query()
            ->where('from_currency', 'USD')
            ->max('recorded_at');

        return [
            'platforms' => $platforms,
            'processors' => $processors,
            'usd_to_pkr' => $usdToPkr,
            'usd_to_eur' => $usdToEur,
            'usd_to_gbp' => $usdToGbp,
            'exchange_rates' => ExchangeRate::query()
                ->where('from_currency', 'USD')
                ->get()
                ->keyBy('to_currency')
                ->map(fn ($row) => (float) $row->rate)
                ->all(),
            'rates_updated_at' => $latestRecordedAt
                ? Carbon::parse($latestRecordedAt)->toIso8601String()
                : null,
            'platform_rates' => $platformRows->map(fn ($row) => [
                'platform' => $row->platform,
                'fee_type' => $row->fee_type,
                'fee_value' => (float) $row->fee_value,
                'currency' => $row->currency,
            ])->values()->all(),
        ];
    }

    public function calculate(float $amount, string $platform, string $processor, string $currency): array
    {
        $rates = $this->getRatesForFrontend();
        $grossUsd = $this->toUsd($amount, $currency, $rates);
        $pkrRate = $rates['usd_to_pkr'];

        $platformPct = $rates['platforms'][$platform]['fee_percent'] ?? 0.0;
        $platformFeeUsd = round($grossUsd * ($platformPct / 100), 2);
        $afterPlatformUsd = $grossUsd - $platformFeeUsd;

        $processorFeeUsd = $this->processorFeeUsd($afterPlatformUsd, $processor, $rates);
        $bankFeeUsd = $this->bankFeeUsd($processor, $rates);

        $netUsd = round(max($afterPlatformUsd - $processorFeeUsd - $bankFeeUsd, 0), 2);
        $netPkr = round($netUsd * $pkrRate, 2);

        $breakdown = $this->buildBreakdown(
            $amount,
            $currency,
            $grossUsd,
            $platformFeeUsd,
            $processorFeeUsd,
            $bankFeeUsd,
            $netUsd,
            $netPkr,
            $pkrRate,
            $platform,
            $processor,
            $rates
        );

        return [
            'gross' => $amount,
            'gross_usd' => round($grossUsd, 2),
            'platform_fee' => $platformFeeUsd,
            'processor_fee' => $processorFeeUsd,
            'conversion_rate' => $pkrRate,
            'bank_fee' => $bankFeeUsd,
            'net_pkr' => $netPkr,
            'net_usd' => $netUsd,
            'breakdown' => $breakdown,
        ];
    }

    private function toUsd(float $amount, string $currency, array $rates): float
    {
        return match (strtoupper($currency)) {
            'USD' => $amount,
            'EUR' => $rates['usd_to_eur'] > 0 ? $amount / $rates['usd_to_eur'] : $amount,
            'GBP' => $rates['usd_to_gbp'] > 0 ? $amount / $rates['usd_to_gbp'] : $amount,
            default => $amount,
        };
    }

    private function processorFeeUsd(float $afterPlatformUsd, string $processor, array $rates): float
    {
        $config = $rates['processors'][$processor] ?? ['fee_type' => 'percent', 'fee_percent' => 0.0];

        if ($config['fee_type'] === 'flat') {
            return 0.0;
        }

        return round($afterPlatformUsd * ((float) $config['fee_percent'] / 100), 2);
    }

    private function bankFeeUsd(string $processor, array $rates): float
    {
        if ($processor === 'direct') {
            return (float) ($rates['processors']['direct']['fee_flat_usd'] ?? 0.0);
        }

        return 0.0;
    }

    private function buildBreakdown(
        float $gross,
        string $currency,
        float $grossUsd,
        float $platformFeeUsd,
        float $processorFeeUsd,
        float $bankFeeUsd,
        float $netUsd,
        float $netPkr,
        float $pkrRate,
        string $platform,
        string $processor,
        array $rates
    ): array {
        $platformLabel = ucfirst($platform);
        $platformPct = $rates['platforms'][$platform]['fee_percent'] ?? 0;
        $processorPct = $rates['processors'][$processor]['fee_percent'] ?? 0;

        $lines = [
            [
                'label' => 'Gross earnings',
                'usd' => round($grossUsd, 2),
                'pkr' => round($grossUsd * $pkrRate, 2),
                'type' => 'credit',
            ],
            [
                'label' => "{$platformLabel} platform fee ({$platformPct}%)",
                'usd' => -$platformFeeUsd,
                'pkr' => -round($platformFeeUsd * $pkrRate, 2),
                'type' => 'deduction',
            ],
        ];

        if ($processorFeeUsd > 0) {
            $processorLabel = ucfirst($processor);
            $lines[] = [
                'label' => "{$processorLabel} transfer fee ({$processorPct}%)",
                'usd' => -$processorFeeUsd,
                'pkr' => -round($processorFeeUsd * $pkrRate, 2),
                'type' => 'deduction',
            ];
        }

        if ($bankFeeUsd > 0) {
            $lines[] = [
                'label' => 'Local bank withdrawal fee (HBL flat)',
                'usd' => -$bankFeeUsd,
                'pkr' => -round($bankFeeUsd * $pkrRate, 2),
                'type' => 'deduction',
            ];
        }

        $lines[] = [
            'label' => 'You keep (net)',
            'usd' => $netUsd,
            'pkr' => $netPkr,
            'type' => 'total',
        ];

        return $lines;
    }
}
