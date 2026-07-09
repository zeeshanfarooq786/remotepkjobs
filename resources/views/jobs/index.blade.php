@extends('layouts.app')

@section('title', 'Remote Developer Jobs — Laravel, Python, React | DevRates')
@section('meta_description', 'Browse fresh remote developer jobs for Laravel, Python, React, and Node. Filter by country, salary, and remote type. Updated daily for freelancers in Pakistan and worldwide.')

@section('content')
    @php
        $activeFilterCount = collect(['search', 'stack', 'country', 'salary_min', 'salary_max', 'remote_type'])
            ->filter(fn ($key) => ! empty($filters[$key] ?? null))
            ->count();
    @endphp

    <div class="mb-8">
        <h1 class="text-3xl font-bold" style="color: var(--color-text-primary);">Remote Developer Jobs</h1>
        <p class="mt-2" style="color: var(--color-text-secondary);">Find verified remote roles with salary ranges and direct apply links.</p>
    </div>

    <form
        method="GET"
        action="{{ route('jobs.index') }}"
        x-data="{ searchDebounce: null }"
        class="mb-6 rounded-xl border p-3 card"
        style="background: var(--color-bg-secondary); border-color: var(--color-card-border);"
    >
        <div class="space-y-3">
            <div class="relative">
                <input
                    type="search"
                    id="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search jobs, companies, or skills..."
                    class="input-field h-9 w-full rounded-lg border px-3 text-sm placeholder:text-[var(--color-text-muted)]"
                    @keydown.enter.prevent="$el.form.submit()"
                    @input="clearTimeout(searchDebounce); searchDebounce = setTimeout(() => $el.form.submit(), 500)"
                >
            </div>

            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                @if ($activeFilterCount > 0)
                    <span class="tag-brand inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium lg:hidden">
                        Filters ({{ $activeFilterCount }})
                    </span>
                @endif

                <select
                    id="stack"
                    name="stack"
                    class="input-field h-9 w-full rounded-lg border px-3 text-sm lg:w-auto lg:min-w-[8rem]"
                    @change="$el.closest('form').submit()"
                >
                    <option value="">All stacks</option>
                    @foreach ($filterOptions['stacks'] as $stack)
                        <option value="{{ $stack }}" @selected(($filters['stack'] ?? '') === $stack)>{{ $stack }}</option>
                    @endforeach
                </select>

                <select
                    id="country"
                    name="country"
                    class="input-field h-9 w-full rounded-lg border px-3 text-sm lg:w-auto lg:min-w-[8rem]"
                    @change="$el.closest('form').submit()"
                >
                    <option value="">All countries</option>
                    @foreach ($filterOptions['countries'] as $country)
                        <option value="{{ $country }}" @selected(($filters['country'] ?? '') === $country)>{{ $country }}</option>
                    @endforeach
                </select>

                <input
                    type="number"
                    id="salary_min"
                    name="salary_min"
                    min="0"
                    placeholder="Min $"
                    value="{{ $filters['salary_min'] ?? '' }}"
                    class="input-field h-9 w-full rounded-lg border px-3 text-sm placeholder:text-[var(--color-text-muted)] lg:w-24"
                    @change="$el.closest('form').submit()"
                >

                <input
                    type="number"
                    id="salary_max"
                    name="salary_max"
                    min="0"
                    placeholder="Max $"
                    value="{{ $filters['salary_max'] ?? '' }}"
                    class="input-field h-9 w-full rounded-lg border px-3 text-sm placeholder:text-[var(--color-text-muted)] lg:w-24"
                    @change="$el.closest('form').submit()"
                >

                <select
                    id="remote_type"
                    name="remote_type"
                    class="input-field h-9 w-full rounded-lg border px-3 text-sm lg:w-auto lg:min-w-[9rem]"
                    @change="$el.closest('form').submit()"
                >
                    <option value="">All types</option>
                    @foreach ($filterOptions['remote_types'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['remote_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <a
                    href="{{ route('jobs.index') }}"
                    class="btn-outline flex h-9 items-center justify-center rounded-lg border px-4 text-sm font-medium lg:ml-auto"
                >
                    Clear
                </a>
            </div>
        </div>
    </form>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <p class="mb-4 text-sm" style="color: var(--color-text-secondary);">
                Showing {{ number_format($jobs->total()) }} {{ \Illuminate\Support\Str::plural('job', $jobs->total()) }}
            </p>

            @if ($jobs->isEmpty())
                <div class="rounded-xl border border-dashed p-8 text-center" style="background: var(--color-bg-secondary); border-color: var(--color-card-border);">
                    <p class="text-lg font-semibold" style="color: var(--color-text-primary);">No jobs match your filters right now</p>
                    <p class="mt-2 text-sm" style="color: var(--color-text-secondary);">Try clearing filters or check back tomorrow — our agent refreshes listings daily.</p>
                    <a href="{{ route('jobs.index') }}" class="btn-primary mt-4 inline-block rounded-full px-5 py-2 text-sm font-semibold">View all jobs</a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($jobs as $job)
                        <article class="card-accent flex flex-col gap-4 rounded-xl border border-l-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5 card" style="border-color: var(--color-card-border); border-left-color: var(--color-card-border-accent);">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm" style="color: var(--color-text-secondary);">{{ $job->company }} · {{ $job->country }}</p>
                                <h2 class="mt-1 text-xl font-bold" style="color: var(--color-text-primary);">
                                    <a href="{{ route('jobs.show', $job->slug) }}" class="link-brand hover:underline">{{ $job->title }}</a>
                                </h2>
                                <p class="mt-2 text-base font-bold" style="color: var(--color-salary);">
                                    @if ($job->salary_min > 0 && $job->salary_max > 0)
                                        USD {{ number_format($job->salary_min) }} – {{ number_format($job->salary_max) }} /mo
                                    @else
                                        <span class="text-sm" style="color: var(--color-text-muted);">Salary not disclosed</span>
                                    @endif
                                </p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="tag-brand rounded-full px-2.5 py-1 text-xs font-medium">{{ $job->stack }}</span>
                                    <span class="text-xs" style="color: var(--color-text-muted);">Posted {{ $job->posted_at?->diffForHumans() ?? 'recently' }}</span>
                                </div>
                            </div>
                            <div class="shrink-0 sm:self-center">
                                <a href="{{ route('jobs.show', $job->slug) }}" class="btn-primary inline-flex w-full items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold sm:w-auto">
                                    View &amp; Apply
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6" style="color: var(--color-text-secondary);">
                    {{ $jobs->links() }}
                </div>
            @endif
        </div>

        <aside class="space-y-4">
            <div class="rounded-xl border p-4 sm:p-5 card" style="border-color: var(--color-card-border);">
                <h2 class="border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">Average monthly salaries</h2>
                <ul class="mt-4 space-y-3">
                    @forelse ($salarySummary as $snapshot)
                        <li class="flex items-center justify-between text-sm">
                            <a href="{{ route('jobs.salary', [strtolower($snapshot->stack), strtolower($snapshot->country)]) }}" class="link-brand hover:underline" style="color: var(--color-text-secondary);">
                                {{ $snapshot->stack }} · {{ $snapshot->country }}
                            </a>
                            <span class="font-semibold" style="color: var(--color-salary);">${{ number_format($snapshot->avg_salary) }}/mo</span>
                        </li>
                    @empty
                        <li class="text-sm" style="color: var(--color-text-muted);">Salary data coming soon.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-xl border p-4 sm:p-5 card" style="background: var(--color-brand-light); border-color: var(--color-brand);">
                <h2 class="text-base font-semibold" style="color: var(--color-text-primary);">What will you actually keep?</h2>
                <p class="mt-2 text-sm" style="color: var(--color-text-secondary);">After Upwork fees and Payoneer withdrawal, your take-home in PKR is often 20–30% less than the contract rate.</p>
                <a href="{{ route('calculator.index') }}" class="btn-primary mt-4 inline-block rounded-full px-4 py-2 text-sm font-semibold">Open calculator</a>
            </div>
        </aside>
    </div>
@endsection
