<?php

namespace App\Services;

use App\Models\PlatformRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlatformRateService
{
    public function bulkUpsert(array $rates, ?string $effectiveDate = null): int
    {
        $date = $effectiveDate
            ? Carbon::parse($effectiveDate)->toDateString()
            : now()->toDateString();

        $updated = 0;

        DB::transaction(function () use ($rates, $date, &$updated) {
            foreach ($rates as $row) {
                PlatformRate::updateOrCreate(
                    [
                        'platform' => $row['platform'],
                        'fee_type' => $row['fee_type'],
                    ],
                    [
                        'fee_value' => $row['fee_value'],
                        'currency' => $row['currency'] ?? 'USD',
                        'effective_date' => $row['effective_date'] ?? $date,
                    ]
                );
                $updated++;
            }
        });

        return $updated;
    }
}
