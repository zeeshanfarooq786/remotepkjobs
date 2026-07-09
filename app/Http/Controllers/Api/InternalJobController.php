<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internal\StoreInternalJobRequest;
use App\Http\Requests\Internal\StoreSalarySnapshotRequest;
use App\Services\JobIngestionService;
use Illuminate\Http\JsonResponse;

class InternalJobController extends Controller
{
    public function __construct(
        private readonly JobIngestionService $ingestion
    ) {}

    public function store(StoreInternalJobRequest $request): JsonResponse
    {
        $result = $this->ingestion->storeJob($request->validated());

        if ($result['status'] === 'duplicate') {
            return response()->json([
                'message' => 'Job with this source_url already exists.',
            ], 409);
        }

        return response()->json([
            'message' => 'Job created.',
            'job' => $result['job'],
        ], 201);
    }

    public function salarySnapshot(StoreSalarySnapshotRequest $request): JsonResponse
    {
        $snapshot = $this->ingestion->storeSalarySnapshot($request->validated());

        return response()->json([
            'message' => 'Salary snapshot recorded.',
            'snapshot' => $snapshot,
        ], 201);
    }
}
