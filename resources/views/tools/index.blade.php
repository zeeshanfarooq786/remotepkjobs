@extends('layouts.app')

@section('title', 'Self-Hosted Dev Tool Alternatives — DevRates')
@section('meta_description', 'Open-source alternatives to Pusher, Mailgun, Heroku, Datadog, Amazon SQS and more. Compare GitHub stars, Docker support, and monthly savings for Laravel developers.')

@push('schema')
    @php
        $listItems = collect($groupedAlternatives)->flatten(1)->values();
    @endphp
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Self-Hosted Developer Tool Alternatives',
            'description' => 'Open-source replacements for paid SaaS tools used by Laravel and PHP developers.',
            'numberOfItems' => $listItems->count(),
            'itemListElement' => $listItems->map(fn ($alt, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'item' => [
                    '@type' => 'SoftwareApplication',
                    'name' => $alt->open_tool,
                    'applicationCategory' => $categoryLabels[$alt->category] ?? ucfirst($alt->category),
                    'description' => $alt->description,
                    'url' => route('tools.show', $alt->slug),
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => '0',
                        'priceCurrency' => 'USD',
                    ],
                ],
            ])->values()->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold" style="color: var(--color-text-primary);">Self-Hosted Alternatives</h1>
        <p class="mt-2" style="color: var(--color-text-secondary);">Replace expensive SaaS tools with open-source options. Grouped by category with live GitHub stats and estimated savings.</p>
    </div>

    <form
        method="GET"
        action="{{ route('tools.index') }}"
        class="mb-8 rounded-xl border p-4 card"
        style="background: var(--color-bg-secondary); border-color: var(--color-card-border);"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid flex-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="sort" class="mb-1 block text-xs font-medium uppercase tracking-wide" style="color: var(--color-text-muted);">Sort by</label>
                    <select
                        id="sort"
                        name="sort"
                        class="input-field h-9 w-full rounded-lg border px-3 text-sm"
                        onchange="this.form.submit()"
                    >
                        <option value="stars" @selected(($filters['sort'] ?? 'stars') === 'stars')>Most stars</option>
                        <option value="updated" @selected(($filters['sort'] ?? '') === 'updated')>Recently updated</option>
                        <option value="savings" @selected(($filters['sort'] ?? '') === 'savings')>Most money saved</option>
                    </select>
                </div>

                <div>
                    <label for="php_version" class="mb-1 block text-xs font-medium uppercase tracking-wide" style="color: var(--color-text-muted);">PHP version</label>
                    <select
                        id="php_version"
                        name="php_version"
                        class="input-field h-9 w-full rounded-lg border px-3 text-sm"
                        onchange="this.form.submit()"
                    >
                        <option value="">Any PHP</option>
                        @foreach ($phpVersions as $version)
                            <option value="{{ $version }}" @selected(($filters['php_version'] ?? '') === $version)>PHP {{ $version }}+</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-4 sm:col-span-2 lg:col-span-2">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm" style="color: var(--color-text-secondary);">
                        <input
                            type="checkbox"
                            name="docker"
                            value="1"
                            class="rounded border text-[var(--color-brand)] focus:ring-[var(--color-brand)]"
                            style="border-color: var(--color-card-border);"
                            @checked($filters['docker'] ?? false)
                            onchange="this.form.submit()"
                        >
                        Docker support
                    </label>
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm" style="color: var(--color-text-secondary);">
                        <input
                            type="checkbox"
                            name="laravel"
                            value="1"
                            class="rounded border text-[var(--color-brand)] focus:ring-[var(--color-brand)]"
                            style="border-color: var(--color-card-border);"
                            @checked($filters['laravel'] ?? false)
                            onchange="this.form.submit()"
                        >
                        Laravel compatible
                    </label>
                </div>
            </div>

            @if (! empty(array_filter($filters ?? [])))
                <a href="{{ route('tools.index') }}" class="link-brand text-sm hover:underline">Clear filters</a>
            @endif
        </div>
    </form>

    @if (empty($groupedAlternatives))
        <div class="rounded-xl border border-dashed p-8 text-center" style="background: var(--color-bg-secondary); border-color: var(--color-card-border);">
            <p style="color: var(--color-text-secondary);">No alternatives match your filters.</p>
            <a href="{{ route('tools.index') }}" class="link-brand mt-3 inline-block text-sm hover:underline">Reset filters</a>
        </div>
    @else
        <div class="space-y-10">
            @foreach ($groupedAlternatives as $category => $alternatives)
                <section>
                    <h2 class="mb-4 text-xl font-bold" style="color: var(--color-text-primary);">
                        {{ $categoryLabels[$category] ?? ucfirst($category) }}
                    </h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($alternatives as $alternative)
                            <article class="card-accent flex flex-col rounded-xl border border-l-4 p-4 sm:p-5 card" style="border-color: var(--color-card-border); border-left-color: var(--color-card-border-accent);">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">{{ $alternative->paid_tool }}</p>
                                        <h3 class="mt-1 text-lg font-bold" style="color: var(--color-text-primary);">
                                            <a href="{{ route('tools.show', $alternative->slug) }}" class="link-brand hover:underline">
                                                {{ $alternative->open_tool }}
                                            </a>
                                        </h3>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" style="background: var(--color-brand-light); color: var(--color-salary);">
                                        Save ${{ number_format($alternative->monthly_cost_paid, 0) }}/mo
                                    </span>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($alternative->github_stars > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs" style="background: var(--color-bg-secondary); color: var(--color-text-secondary);">
                                            <span aria-hidden="true">&#9733;</span>
                                            {{ number_format($alternative->github_stars) }} stars
                                        </span>
                                    @endif

                                    @if ($alternative->last_commit)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs" style="background: var(--color-bg-secondary); color: var(--color-text-secondary);">
                                            Updated {{ $alternative->last_commit->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs" style="background: var(--color-bg-secondary); color: var(--color-text-muted);">
                                            No recent commits
                                        </span>
                                    @endif

                                    @if ($alternative->laravel_compatible)
                                        <span class="tag-brand inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium">
                                            Laravel compatible
                                        </span>
                                    @endif

                                    @if ($alternative->docker_support)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs" style="background: var(--color-bg-secondary); color: var(--color-text-secondary);">
                                            Docker
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-3 flex-1 text-sm line-clamp-2" style="color: var(--color-text-secondary);">{{ $alternative->description }}</p>

                                <a
                                    href="{{ route('tools.show', $alternative->slug) }}"
                                    class="btn-primary mt-4 inline-flex w-full items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold sm:w-auto"
                                >
                                    Compare with {{ $alternative->paid_tool }}
                                </a>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection
