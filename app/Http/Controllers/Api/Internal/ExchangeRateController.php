<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internal\BulkExchangeRateRequest;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;

class ExchangeRateController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRates
    ) {}

    public function store(BulkExchangeRateRequest $request): JsonResponse
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
}
