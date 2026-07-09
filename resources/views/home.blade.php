@extends('layouts.app')

@section('content')
    {{-- 1. Hero --}}
    <section class="mb-12 text-center">
        <h1 class="text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl" style="color: var(--color-text-primary);">
            What do remote developers actually earn — and keep?
        </h1>
        <p class="mx-auto mt-4 max-w-2xl text-base sm:text-lg" style="color: var(--color-text-secondary);">
            Real salary data, live fee calculations, and self-hosted tool savings — built for freelance developers worldwide.
        </p>
        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a
                href="{{ route('jobs.index') }}"
                class="btn-primary inline-flex w-full items-center justify-center rounded-full px-6 py-3 text-sm font-semibold sm:w-auto"
            >
                Browse remote jobs
            </a>
            <a
                href="{{ route('calculator.index') }}"
                class="btn-outline inline-flex w-full items-center justify-center rounded-full border px-6 py-3 text-sm font-semibold sm:w-auto"
            >
                Calculate take-home pay
            </a>
        </div>
    </section>

    {{-- 2. Stats bar --}}
    <section class="mb-12 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border p-5 text-center card" style="border-color: var(--color-card-border);">
            <p class="text-2xl font-bold" style="color: var(--color-brand);">{{ number_format($stats['total_jobs']) }}</p>
            <p class="mt-1 text-sm" style="color: var(--color-text-secondary);">Jobs listed</p>
        </div>
        <div class="rounded-xl border p-5 text-center card" style="border-color: var(--color-card-border);">
            <p class="text-2xl font-bold" style="color: var(--color-salary);">
                @if ($stats['avg_laravel_salary'])
                    ${{ number_format($stats['avg_laravel_salary']) }}/mo
                @else
                    —
                @endif
            </p>
            <p class="mt-1 text-sm" style="color: var(--color-text-secondary);">Avg Laravel dev salary</p>
        </div>
        <div class="rounded-xl border p-5 text-center card" style="border-color: var(--color-card-border);">
            <p class="text-base font-bold sm:text-lg" style="color: var(--color-text-primary);">
                <a href="{{ $stats['top_calculator']['url'] }}" class="link-brand hover:underline">
                    {{ $stats['top_calculator']['label'] }}
                </a>
            </p>
            <p class="mt-1 text-sm" style="color: var(--color-text-secondary);">Top calculator search this week</p>
        </div>
    </section>

    {{-- 3. Mini calculator --}}
    <section class="mb-12">
        <div class="mb-4">
            <h2 class="text-xl font-bold" style="color: var(--color-text-primary);">Quick take-home estimate</h2>
            <p class="mt-1 text-sm" style="color: var(--color-text-secondary);">Enter an amount and platform to see your net pay instantly.</p>
        </div>

        <script>
            window.DevRates = {
                rates: @json($rates),
                defaults: { amount: 1000, platform: 'upwork', processor: 'payoneer', currency: 'USD' },
                logUrl: null,
                csrfToken: null,
            };
        </script>

        @include('calculator.partials.mini-widget')
    </section>

    {{-- 4. Latest jobs --}}
    <section class="mb-12">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold" style="color: var(--color-text-primary);">Latest remote jobs</h2>
                <p class="mt-1 text-sm" style="color: var(--color-text-secondary);">Fresh listings updated daily by our job agent.</p>
            </div>
            <a href="{{ route('jobs.index') }}" class="link-brand shrink-0 text-sm hover:underline">View all &rarr;</a>
        </div>

        @if ($latestJobs->isEmpty())
            <div class="rounded-xl border border-dashed p-8 text-center" style="background: var(--color-bg-secondary); border-color: var(--color-card-border);">
                <p style="color: var(--color-text-secondary);">No jobs listed yet. Check back tomorrow.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latestJobs as $job)
                    <article class="card-accent flex flex-col rounded-xl border border-l-4 p-4 card" style="border-color: var(--color-card-border); border-left-color: var(--color-card-border-accent);">
                        <p class="text-xs" style="color: var(--color-text-muted);">{{ $job->company }} &middot; {{ $job->country }}</p>
                        <h3 class="mt-1 text-base font-bold" style="color: var(--color-text-primary);">
                            <a href="{{ route('jobs.show', $job->slug) }}" class="link-brand hover:underline">{{ $job->title }}</a>
                        </h3>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--color-salary);">
                            @if ($job->salary_min > 0 && $job->salary_max > 0)
                                USD {{ number_format($job->salary_min) }} – {{ number_format($job->salary_max) }}/mo
                            @else
                                <span style="color: var(--color-text-muted);">Salary not disclosed</span>
                            @endif
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="tag-brand rounded-full px-2.5 py-1 text-xs font-medium">{{ $job->stack }}</span>
                            <span class="text-xs" style="color: var(--color-text-muted);">{{ $job->posted_at?->diffForHumans() ?? 'Recently' }}</span>
                        </div>
                        <a href="{{ route('jobs.show', $job->slug) }}" class="link-brand mt-4 inline-flex items-center text-sm font-medium hover:underline">
                            View job &rarr;
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- 5. Top alternatives --}}
    <section class="mb-12">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold" style="color: var(--color-text-primary);">Top self-hosted alternatives</h2>
                <p class="mt-1 text-sm" style="color: var(--color-text-secondary);">Replace expensive SaaS with open-source tools developers actually use.</p>
            </div>
            <a href="{{ route('tools.index') }}" class="link-brand shrink-0 text-sm hover:underline">Browse all &rarr;</a>
        </div>

        @if ($topAlternatives->isEmpty())
            <div class="rounded-xl border border-dashed p-8 text-center" style="background: var(--color-bg-secondary); border-color: var(--color-card-border);">
                <p style="color: var(--color-text-secondary);">Alternative tools coming soon.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($topAlternatives as $alternative)
                    <article class="card-accent flex flex-col rounded-xl border border-l-4 p-4 sm:p-5 card" style="border-color: var(--color-card-border); border-left-color: var(--color-card-border-accent);">
                        <p class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">{{ $alternative->paid_tool }}</p>
                        <h3 class="mt-1 text-lg font-bold" style="color: var(--color-text-primary);">
                            <a href="{{ route('tools.show', $alternative->slug) }}" class="link-brand hover:underline">
                                {{ $alternative->open_tool }}
                            </a>
                        </h3>
                        <p class="mt-2 flex-1 text-sm line-clamp-3" style="color: var(--color-text-secondary);">{{ $alternative->description }}</p>
                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span style="color: var(--color-text-muted);">★ {{ number_format($alternative->github_stars) }}</span>
                            <span class="font-semibold" style="color: var(--color-salary);">Save ${{ number_format($alternative->monthly_cost_paid, 0) }}/mo</span>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- 6. SEO footer text --}}
    @if ($footerPage?->content['paragraph'] ?? null)
        <section class="rounded-xl border p-6 card" style="background: var(--color-bg-secondary); border-color: var(--color-card-border);">
            <p class="text-sm leading-relaxed" style="color: var(--color-text-secondary);">{{ $footerPage->content['paragraph'] }}</p>
        </section>
    @endif
@endsection
