<?php

namespace App\Services;

use App\Models\Job;
use App\Models\SalarySnapshot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class JobService
{
    private const ALLOWED_STACKS = ['Laravel', 'Python', 'React', 'Node'];

    private const REMOTE_TYPE_MAP = [
        'full' => 'fully_remote',
        'part' => 'part_time_remote',
        'contract' => 'contract',
    ];

    public function getFilteredJobs(array $filters, int $page = 1): LengthAwarePaginator
    {
        $query = Job::query()
            ->where('is_active', true)
            ->orderByDesc('posted_at');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('company', 'like', '%'.$search.'%')
                    ->orWhere('stack', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['stack']) && in_array($filters['stack'], self::ALLOWED_STACKS, true)) {
            $query->where('stack', $filters['stack']);
        }

        if (! empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (! empty($filters['remote_type']) && isset(self::REMOTE_TYPE_MAP[$filters['remote_type']])) {
            $query->where('remote_type', self::REMOTE_TYPE_MAP[$filters['remote_type']]);
        }

        if (! empty($filters['salary_min'])) {
            $query->where('salary_max', '>=', (int) $filters['salary_min']);
        }

        if (! empty($filters['salary_max'])) {
            $query->where('salary_min', '<=', (int) $filters['salary_max']);
        }

        return $query->paginate(20, ['*'], 'page', $page)->withQueryString();
    }

    public function getSalaryData(string $stack, string $country): array
    {
        $stackLabel = ucfirst(strtolower($stack));
        $countryLabel = ucfirst(strtolower($country));

        $latest = SalarySnapshot::query()
            ->where('stack', $stackLabel)
            ->where('country', $countryLabel)
            ->orderByDesc('recorded_at')
            ->first();

        $history = SalarySnapshot::query()
            ->where('stack', $stackLabel)
            ->where('country', $countryLabel)
            ->where('recorded_at', '>=', now()->subMonths(6))
            ->orderBy('recorded_at')
            ->get();

        if ($history->isEmpty() && $latest) {
            $history = collect([$latest]);
        }

        $chartLabels = $history->map(fn ($row) => $row->recorded_at->format('M Y'))->values()->all();
        $chartValues = $history->map(fn ($row) => $row->avg_salary)->values()->all();

        $topCompanies = Job::query()
            ->where('is_active', true)
            ->where('stack', $stackLabel)
            ->where('country', $countryLabel)
            ->select('company')
            ->selectRaw('COUNT(*) as job_count')
            ->selectRaw('AVG((salary_min + salary_max) / 2) as avg_mid_salary')
            ->groupBy('company')
            ->orderByDesc('job_count')
            ->limit(5)
            ->get();

        return [
            'stack' => $stackLabel,
            'country' => $countryLabel,
            'avg_salary' => $latest?->avg_salary ?? 0,
            'sample_size' => $latest?->sample_size ?? 0,
            'recorded_at' => $latest?->recorded_at,
            'chart_labels' => $chartLabels,
            'chart_values' => $chartValues,
            'top_companies' => $topCompanies,
        ];
    }

    public function getRelatedJobs(Job $job, int $limit = 5): Collection
    {
        return Job::query()
            ->where('is_active', true)
            ->where('stack', $job->stack)
            ->where('id', '!=', $job->id)
            ->orderByDesc('posted_at')
            ->limit($limit)
            ->get();
    }

    public function generateSlug(array $jobData): string
    {
        $base = Str::slug(($jobData['title'] ?? 'job').'-'.($jobData['company'] ?? 'company'));
        $slug = $base;
        $counter = 1;

        while (Job::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function getSidebarSalarySummary(): array
    {
        return SalarySnapshot::query()
            ->whereIn('stack', self::ALLOWED_STACKS)
            ->orderByDesc('recorded_at')
            ->get()
            ->unique(fn ($row) => $row->stack.'-'.$row->country)
            ->take(6)
            ->values()
            ->all();
    }

    public function getFilterOptions(): array
    {
        return [
            'stacks' => self::ALLOWED_STACKS,
            'countries' => Job::query()
                ->where('is_active', true)
                ->distinct()
                ->orderBy('country')
                ->pluck('country')
                ->all(),
            'remote_types' => [
                'full' => 'Fully remote',
                'part' => 'Part-time remote',
                'contract' => 'Contract',
            ],
        ];
    }
}
