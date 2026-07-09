@extends('layouts.app')

@section('title', $alternative->paid_tool.' Alternative: '.$alternative->open_tool.' | DevRates')
@section('meta_description', 'Replace '.$alternative->paid_tool.' with '.$alternative->open_tool.'. Save $'.number_format($alternative->monthly_cost_paid, 0).'/mo with this self-hosted Laravel-compatible alternative.')

@php
    $comparison = $alternative->comparison ?? [];
    $features = $comparison['features'] ?? [];
    $installSnippet = $comparison['install_snippet'] ?? null;
@endphp

@push('schema')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $alternative->open_tool,
            'alternateName' => $alternative->paid_tool.' alternative',
            'applicationCategory' => \App\Services\AlternativeService::CATEGORY_LABELS[$alternative->category] ?? ucfirst($alternative->category),
            'description' => $alternative->description,
            'url' => route('tools.show', $alternative->slug),
            'operatingSystem' => 'Linux, Docker',
            'softwareRequirements' => $alternative->php_version_req ? 'PHP '.$alternative->php_version_req.'+' : null,
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
                'description' => 'Self-hosted; replaces '.$alternative->paid_tool.' at $'.number_format($alternative->monthly_cost_paid, 0).'/month',
            ],
            'aggregateRating' => $alternative->github_stars > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => min(5, max(1, round($alternative->github_stars / 5000, 1))),
                'ratingCount' => $alternative->github_stars,
                'bestRating' => 5,
                'worstRating' => 1,
            ] : null,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@section('content')
    <div class="mb-8">
        <p class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">
            {{ \App\Services\AlternativeService::CATEGORY_LABELS[$alternative->category] ?? ucfirst($alternative->category) }}
        </p>
        <h1 class="mt-2 text-3xl font-bold" style="color: var(--color-text-primary);">
            {{ $alternative->paid_tool }} &rarr; {{ $alternative->open_tool }}
        </h1>
        <p class="mt-3" style="color: var(--color-text-secondary);">{{ $alternative->description }}</p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border p-4 sm:p-6 card" style="border-color: var(--color-card-border);">
                <h2 class="border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">Feature comparison</h2>

                @if (count($features) > 0)
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[28rem] text-left text-sm">
                            <thead>
                                <tr class="border-b text-xs uppercase tracking-wide" style="border-color: var(--color-card-border); color: var(--color-text-muted);">
                                    <th class="py-3 pr-4 font-medium">Feature</th>
                                    <th class="py-3 pr-4 font-medium">{{ $alternative->paid_tool }}</th>
                                    <th class="py-3 font-medium">{{ $alternative->open_tool }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($features as $row)
                                    <tr class="border-b" style="border-color: var(--color-bg-tertiary);">
                                        <td class="py-3 pr-4 font-medium" style="color: var(--color-text-secondary);">{{ $row['feature'] }}</td>
                                        <td class="py-3 pr-4" style="color: var(--color-text-secondary);">{{ $row['paid'] }}</td>
                                        <td class="py-3" style="color: var(--color-salary);">{{ $row['open'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Paid tool</dt>
                            <dd class="mt-1" style="color: var(--color-text-secondary);">{{ $alternative->paid_tool }} — ${{ number_format($alternative->monthly_cost_paid, 0) }}/mo</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Open alternative</dt>
                            <dd class="mt-1 font-semibold" style="color: var(--color-salary);">{{ $alternative->open_tool }} — Free (self-hosted)</dd>
                        </div>
                    </dl>
                @endif
            </section>

            @if ($installSnippet)
                <section class="rounded-xl border p-4 sm:p-6 card" style="border-color: var(--color-card-border);">
                    <h2 class="border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">Installation</h2>
                    <pre class="mt-4 overflow-x-auto rounded-lg p-4 text-sm" style="background: var(--color-bg-secondary); color: var(--color-text-secondary);"><code>{{ $installSnippet }}</code></pre>
                </section>
            @endif

            <section class="rounded-xl border p-4 sm:p-6 card" style="border-color: var(--color-card-border);">
                <h2 class="border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">GitHub stats</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-lg p-4" style="background: var(--color-bg-secondary);">
                        <dt class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Stars</dt>
                        <dd class="mt-1 text-2xl font-bold" style="color: var(--color-text-primary);">{{ number_format($alternative->github_stars) }}</dd>
                    </div>
                    <div class="rounded-lg p-4" style="background: var(--color-bg-secondary);">
                        <dt class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Forks</dt>
                        <dd class="mt-1 text-2xl font-bold" style="color: var(--color-text-primary);">{{ number_format($alternative->github_forks) }}</dd>
                    </div>
                    <div class="rounded-lg p-4" style="background: var(--color-bg-secondary);">
                        <dt class="text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Last commit</dt>
                        <dd class="mt-1 text-lg font-semibold" style="color: var(--color-text-primary);">
                            @if ($alternative->last_commit)
                                {{ $alternative->last_commit->diffForHumans() }}
                            @else
                                <span style="color: var(--color-text-muted);">N/A</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($alternative->laravel_compatible)
                        <span class="tag-brand rounded-full px-2.5 py-1 text-xs font-medium">Laravel compatible</span>
                    @endif
                    @if ($alternative->docker_support)
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs" style="background: var(--color-bg-secondary); color: var(--color-text-secondary);">Docker support</span>
                    @endif
                    @if ($alternative->php_version_req)
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs" style="background: var(--color-bg-secondary); color: var(--color-text-secondary);">PHP {{ $alternative->php_version_req }}+</span>
                    @endif
                </div>
            </section>
        </div>

        <aside class="space-y-4">
            <section
                class="rounded-xl border p-4 sm:p-5 card"
                style="background: var(--color-brand-light); border-color: var(--color-brand);"
                x-data="{ apps: {{ $appCount }} }"
            >
                <h2 class="text-base font-semibold" style="color: var(--color-text-primary);">Cost calculator</h2>
                <p class="mt-2 text-sm" style="color: var(--color-text-secondary);">
                    If you have
                    <input
                        type="number"
                        min="1"
                        max="100"
                        x-model.number="apps"
                        @change="window.location.href = '{{ route('tools.show', $alternative->slug) }}?apps=' + Math.max(1, apps)"
                        class="input-field mx-1 w-14 rounded border px-2 py-0.5 text-center text-sm"
                    >
                    {{ $appCount === 1 ? 'app' : 'apps' }} paying ${{ number_format($alternative->monthly_cost_paid, 0) }} each, that is
                    <strong style="color: var(--color-text-primary);">${{ number_format($monthlyCost, 0) }}/mo</strong>
                    (<strong style="color: var(--color-text-primary);">${{ number_format($yearlyCost, 0) }}/year</strong>).
                </p>
                <p class="mt-3 text-sm font-semibold" style="color: var(--color-salary);">
                    Self-hosting with {{ $alternative->open_tool }} saves ${{ number_format($yearlyCost, 0) }}/year in SaaS fees.
                </p>
                <a
                    href="{{ route('calculator.index') }}"
                    class="btn-primary mt-4 inline-flex w-full items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold"
                >
                    Full freelancer calculator &rarr;
                </a>
            </section>

            <div class="rounded-xl border p-4 card" style="border-color: var(--color-card-border);">
                <h3 class="text-sm font-semibold" style="color: var(--color-text-primary);">Quick summary</h3>
                <ul class="mt-3 space-y-2 text-sm" style="color: var(--color-text-secondary);">
                    <li><span style="color: var(--color-text-muted);">Paid:</span> {{ $alternative->paid_tool }} (${{ number_format($alternative->monthly_cost_paid, 0) }}/app/mo)</li>
                    <li><span style="color: var(--color-text-muted);">Open:</span> {{ $alternative->open_tool }} (VPS cost only)</li>
                    <li><span style="color: var(--color-text-muted);">Per-app savings:</span> <span style="color: var(--color-salary);">${{ number_format($alternative->monthly_cost_paid, 0) }}/mo</span></li>
                </ul>
            </div>

            <a href="{{ route('tools.index') }}" class="link-brand block text-sm hover:underline">&larr; Back to all alternatives</a>
        </aside>
    </div>
@endsection
