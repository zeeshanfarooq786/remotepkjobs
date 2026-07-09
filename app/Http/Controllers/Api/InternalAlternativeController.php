<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internal\UpdateAlternativeRequest;
use App\Services\AlternativeService;
use Illuminate\Http\JsonResponse;

class InternalAlternativeController extends Controller
{
    public function __construct(
        private readonly AlternativeService $alternatives
    ) {}

    public function update(UpdateAlternativeRequest $request): JsonResponse
    {
        $alternative = $this->alternatives->updateGithubStats($request->validated());

        if ($alternative === null) {
            return response()->json([
                'message' => 'Alternative not found for paid_tool.',
            ], 404);
        }

        return response()->json([
            'message' => 'Alternative GitHub stats updated.',
            'alternative' => $alternative,
        ]);
    }
}
