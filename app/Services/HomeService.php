<?php

namespace App\Services;

use App\Models\Alternative;
use App\Models\ExchangeRate;
use App\Models\Job;
use App\Models\Page;
use App\Models\PlatformRate;
use App\Models\SalarySnapshot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HomeService
{
    public function __construct(
        private readonly CalculatorService $calculator
    ) {}

    /**
     * @return array{
     *     stats: array{total_jobs: int, avg_laravel_salary: int|null, top_calculator: array{label: string, url: string}},
     *     latestJobs: Collection,
     *     topAlternatives: Collection,
     *     footerPage: Page|null,
     *     rates: array
     * }
     */
    public function getHomeData(): array
    {
        return [
            'stats' => $this->getStats(),
            'latestJobs' => $this->getLatestJobs(),
            'topAlternatives' => $this->getTopAlternatives(),
            'footerPage' => Page::query()->where('slug', 'home-footer')->first(),
            'rates' => $this->calculator->getRatesForFrontend(),
        ];
    }

    public function getLastDataUpdated(): ?Carbon
    {
        $timestamps = collect([
            Job::query()->max('updated_at'),
            ExchangeRate::query()->max('recorded_at'),
            PlatformRate::query()->max('updated_at'),
            Alternative::query()->max('updated_at'),
        ])->filter();

        if ($timestamps->isEmpty()) {
            return null;
        }

        return Carbon::parse($timestamps->max());
    }

    /**
     * @return array{total_jobs: int, avg_laravel_salary: int|null, top_calculator: array{label: string, url: string}}
     */
    private function getStats(): array
    {
        return [
            'total_jobs' => Job::query()->where('is_active', true)->count(),
            'avg_laravel_salary' => $this->getAverageLaravelSalary(),
            'top_calculator' => $this->getTopCalculatorSearch(),
        ];
    }

    private function getAverageLaravelSalary(): ?int
    {
        $snapshot = SalarySnapshot::query()
            ->where('stack', 'Laravel')
            ->orderByDesc('recorded_at')
            ->first();

        if ($snapshot) {
            return (int) $snapshot->avg_salary;
        }

        $jobs = Job::query()
            ->where('is_active', true)
            ->where('stack', 'Laravel')
            ->where('salary_min', '>', 0)
            ->get(['salary_min', 'salary_max']);

        if ($jobs->isEmpty()) {
            return null;
        }

        $average = $jobs->avg(fn (Job $job) => ($job->salary_min + max($job->salary_max, $job->salary_min)) / 2);

        return $average ? (int) round($average) : null;
    }

    /**
     * @return array{label: string, url: string}
     */
    private function getTopCalculatorSearch(): array
    {
        $page = Page::query()
            ->where('tool_type', 'calculator')
            ->where('updated_at', '>=', now()->subWeek())
            ->orderByDesc('updated_at')
            ->first();

        if (! $page) {
            $page = Page::query()
                ->where('tool_type', 'calculator')
                ->orderByDesc('updated_at')
                ->first();
        }

        if ($page) {
            $label = $page->content['h1'] ?? Str::before($page->title, ' —');

            return [
                'label' => $label,
                'url' => route('calculator.variant', $page->slug),
            ];
        }

        return [
            'label' => 'Freelancer Fee Calculator',
            'url' => route('calculator.index'),
        ];
    }

    private function getLatestJobs(): Collection
    {
        return Job::query()
            ->where('is_active', true)
            ->orderByDesc('posted_at')
            ->limit(6)
            ->get();
    }

    private function getTopAlternatives(): Collection
    {
        return Alternative::query()
            ->orderByDesc('github_stars')
            ->limit(3)
            ->get();
    }
}
