<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internal\BulkExchangeRateRequest;
use App\Http\Requests\Internal\BulkPlatformRateRequest;
use App\Services\ExchangeRateService;
use App\Services\PlatformRateService;
use Illuminate\Http\JsonResponse;

class InternalRateController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRates,
        private readonly PlatformRateService $platformRates
    ) {}

    public function exchangeRates(BulkExchangeRateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $count = $this->exchangeRates->bulkUpsert(
            $validated['rates'],
            $validated['recorded_at'] ?? null
        );

        return response()->json([
            'message' => 'Exchange rates updated.',
            'updated' => $count,
            'recorded_at' => $this->exchangeRates->latestUpdatedAt()?->toIso8601String(),
        ]);
    }

    public function platformRates(BulkPlatformRateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $count = $this->platformRates->bulkUpsert(
            $validated['rates'],
            $validated['effective_date'] ?? null
        );

        return response()->json([
            'message' => 'Platform rates updated.',
            'updated' => $count,
            'effective_date' => ($validated['effective_date'] ?? now()->toDateString()),
        ]);
    }
}
