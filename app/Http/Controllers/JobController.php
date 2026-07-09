<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobFilterRequest;
use App\Models\Job;
use App\Services\JobService;
use App\Services\SeoService;
use Illuminate\View\View;

class JobController extends Controller
{
    public function __construct(
        private readonly JobService $jobService,
        private readonly SeoService $seo
    ) {}

    public function index(JobFilterRequest $request): View
    {
        $filters = $request->validated();
        $page = (int) ($filters['page'] ?? 1);

        return view('jobs.index', [
            'jobs' => $this->jobService->getFilteredJobs($filters, $page),
            'filters' => $filters,
            'filterOptions' => $this->jobService->getFilterOptions(),
            'salarySummary' => $this->jobService->getSidebarSalarySummary(),
            'seo' => $this->seo->getJobsIndexMeta(),
        ]);
    }

    public function show(string $slug): View
    {
        $job = Job::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $midSalary = (int) round((($job->salary_min ?? 0) + ($job->salary_max ?? 0)) / 2);

        return view('jobs.show', [
            'job' => $job,
            'relatedJobs' => $this->jobService->getRelatedJobs($job),
            'calculatorAmount' => max($midSalary, 1000),
            'seo' => $this->seo->getJobMeta($job),
        ]);
    }

    public function salary(string $stack, string $country): View
    {
        $salaryData = $this->jobService->getSalaryData($stack, $country);

        return view('jobs.salary', [
            'salaryData' => $salaryData,
            'calculatorAmount' => max((int) $salaryData['avg_salary'], 1000),
            'seo' => $this->seo->getSalaryMeta($stack, $country),
        ]);
    }
}
