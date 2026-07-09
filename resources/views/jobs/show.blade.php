@extends('layouts.app')

@section('title', $job->title.' at '.$job->company.' — Remote '.$job->stack.' Job | DevRates')
@section('meta_description', 'Remote '.$job->stack.' job at '.$job->company.'. Salary: '.$job->currency.' '.number_format($job->salary_min).'–'.number_format($job->salary_max).'/mo. Posted '.$job->posted_at?->diffForHumans().'. Apply now.')

@section('content')
    @php
        $employmentType = match ($job->remote_type) {
            'part_time_remote' => 'PART_TIME',
            'contract' => 'CONTRACTOR',
            default => 'FULL_TIME',
        };
    @endphp

    <div class="grid gap-8 lg:grid-cols-3">
        <article class="lg:col-span-2">
            <header class="mb-6">
                <p class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">{{ $job->stack }} · {{ $job->country }}</p>
                <h1 class="mt-2 text-3xl font-bold" style="color: var(--color-text-primary);">{{ $job->title }}</h1>
                <p class="mt-2 text-lg" style="color: var(--color-text-secondary);">{{ $job->company }}</p>
                <p class="mt-3 text-xl font-bold" style="color: var(--color-salary);">
                    @if ($job->salary_min > 0 && $job->salary_max > 0)
                        USD {{ number_format($job->salary_min) }} – {{ number_format($job->salary_max) }} /mo
                    @else
                        <span class="text-sm" style="color: var(--color-text-muted);">Salary not disclosed</span>
                    @endif
                </p>
                <p class="mt-2 text-sm" style="color: var(--color-text-muted);">Posted {{ $job->posted_at?->diffForHumans() ?? 'recently' }} · {{ str_replace('_', ' ', ucfirst($job->remote_type)) }}</p>
            </header>

            <section class="rounded-xl border p-4 sm:p-6 card" style="border-color: var(--color-card-border);">
                <h2 class="border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">Job details</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Stack</dt>
                        <dd class="mt-1 text-sm font-medium" style="color: var(--color-text-secondary);">{{ $job->stack }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Country</dt>
                        <dd class="mt-1 text-sm font-medium" style="color: var(--color-text-secondary);">{{ $job->country }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Remote type</dt>
                        <dd class="mt-1 text-sm font-medium" style="color: var(--color-text-secondary);">{{ str_replace('_', ' ', ucfirst($job->remote_type)) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Salary range</dt>
                        <dd class="mt-1 text-sm font-medium" style="color: var(--color-salary);">
                            @if ($job->salary_min > 0 && $job->salary_max > 0)
                                USD {{ number_format($job->salary_min) }} – {{ number_format($job->salary_max) }} /mo
                            @else
                                <span class="text-sm" style="color: var(--color-text-muted);">Salary not disclosed</span>
                            @endif
                        </dd>
                    </div>
                </dl>
                <p class="mt-4 text-sm" style="color: var(--color-text-secondary);">
                    View salary trends for {{ $job->stack }} developers in {{ $job->country }} on our
                    <a href="{{ route('jobs.salary', [strtolower($job->stack), strtolower($job->country)]) }}" class="link-brand font-medium hover:underline">salary data page</a>.
                </p>
            </section>

            @if ($job->salary_min > 0)
                <section class="mt-6 rounded-xl border p-5 sm:p-6 card" style="background: var(--color-brand-light); border-color: var(--color-brand);">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold" style="color: var(--color-text-primary);">Earning ${{ number_format($job->salary_min) }} on Upwork?</h2>
                            <p class="mt-2 text-sm" style="color: var(--color-text-secondary);">
                                See what you actually keep after the 10% Upwork fee, Payoneer withdrawal, and PKR conversion — before you accept this offer.
                            </p>
                        </div>
                        <a
                            href="/calculator?amount={{ $job->salary_min }}&platform=upwork&processor=payoneer"
                            class="btn-primary inline-flex shrink-0 items-center justify-center rounded-full px-6 py-3 text-sm font-semibold"
                        >
                            Calculate My Take-Home &rarr;
                        </a>
                    </div>
                </section>
            @endif

            <div class="mt-8 flex justify-stretch sm:justify-end">
                <a
                    href="{{ $job->source_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex w-full items-center justify-center rounded-lg px-8 py-3.5 text-base font-bold sm:w-auto"
                    style="background: var(--color-success); color: var(--color-text-inverse);"
                >
                    Apply Now &rarr;
                </a>
            </div>
        </article>

        <aside>
            <div class="rounded-xl border p-4 sm:p-5 card" style="border-color: var(--color-card-border);">
                <h2 class="border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">Related jobs</h2>
                @if ($relatedJobs->isEmpty())
                    <p class="mt-3 text-sm" style="color: var(--color-text-muted);">No related listings right now.</p>
                @else
                    <ul class="mt-4 space-y-4">
                        @foreach ($relatedJobs as $related)
                            <li class="border-b pb-4 last:border-0 last:pb-0" style="border-color: var(--color-card-border);">
                                <a href="{{ route('jobs.show', $related->slug) }}" class="link-brand font-medium hover:underline" style="color: var(--color-text-primary);">{{ $related->title }}</a>
                                <p class="mt-1 text-xs" style="color: var(--color-text-muted);">{{ $related->company }}</p>
                                <p class="mt-1 text-xs font-semibold" style="color: var(--color-salary);">${{ number_format($related->salary_min) }}–${{ number_format($related->salary_max) }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </aside>
    </div>

    @push('schema')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'JobPosting',
                'title' => $job->title,
                'description' => 'Remote '.$job->stack.' developer position at '.$job->company.'. Salary range '.$job->currency.' '.number_format($job->salary_min).' to '.number_format($job->salary_max).' per month.',
                'datePosted' => $job->posted_at?->toIso8601String(),
                'validThrough' => $job->posted_at?->copy()->addDays(30)->toIso8601String(),
                'employmentType' => $employmentType,
                'hiringOrganization' => [
                    '@type' => 'Organization',
                    'name' => $job->company,
                ],
                'jobLocation' => [
                    '@type' => 'Place',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressCountry' => $job->country,
                    ],
                ],
                'applicantLocationRequirements' => [
                    '@type' => 'Country',
                    'name' => $job->country,
                ],
                'jobLocationType' => 'TELECOMMUTE',
                'baseSalary' => [
                    '@type' => 'MonetaryAmount',
                    'currency' => $job->currency,
                    'value' => [
                        '@type' => 'QuantitativeValue',
                        'minValue' => $job->salary_min,
                        'maxValue' => $job->salary_max,
                        'unitText' => 'MONTH',
                    ],
                ],
                'directApply' => true,
                'url' => $job->source_url,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @endpush
@endsection
