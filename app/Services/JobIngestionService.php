<?php

namespace App\Services;

use App\Models\Job;
use App\Models\SalarySnapshot;

class JobIngestionService
{
    public function __construct(
        private readonly JobService $jobs
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{status: string, job?: Job}
     */
    public function storeJob(array $data): array
    {
        if (Job::query()->where('source_url', $data['source_url'])->exists()) {
            return ['status' => 'duplicate'];
        }

        $data['slug'] = $this->jobs->generateSlug($data);
        $data['is_active'] = $data['is_active'] ?? true;

        $job = Job::query()->create($data);

        return [
            'status' => 'created',
            'job' => $job,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function storeSalarySnapshot(array $data): SalarySnapshot
    {
        return SalarySnapshot::query()->create([
            'stack' => ucfirst(strtolower($data['stack'])),
            'country' => ucfirst(strtolower($data['country'])),
            'avg_salary' => (int) $data['avg_salary'],
            'sample_size' => (int) $data['sample_size'],
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);
    }
}
