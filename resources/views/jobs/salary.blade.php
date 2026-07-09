@extends('layouts.app')

@section('title', 'Remote '.$salaryData['stack'].' Developer Salary in '.$salaryData['country'].' 2026 | DevRates')
@section('meta_description', 'Average remote '.$salaryData['stack'].' developer salary in '.$salaryData['country'].': $'.number_format($salaryData['avg_salary']).'/mo based on '.$salaryData['sample_size'].' listings. Historical trends and top hiring companies.')

@section('content')
    <div class="mb-8">
        <p class="text-xs uppercase tracking-wide text-slate-500">Salary data</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-100">
            Remote {{ $salaryData['stack'] }} Salary — {{ $salaryData['country'] }}
        </h1>
        <p class="mt-2 text-slate-300">
            Based on {{ $salaryData['sample_size'] }} active listings
            @if ($salaryData['recorded_at'])
                · Updated {{ $salaryData['recorded_at']->diffForHumans() }}
            @endif
        </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-slate-700 bg-slate-800 p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-slate-200 border-b border-slate-700 pb-2">Average monthly salary</h2>
                <p class="mt-4 text-4xl font-bold text-emerald-500">${{ number_format($salaryData['avg_salary']) }}<span class="text-lg font-medium text-slate-500">/mo</span></p>
            </section>

            <section class="rounded-xl border border-slate-700 bg-slate-800 p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-slate-200 border-b border-slate-700 pb-2">6-month trend</h2>
                <div class="mt-4 h-64">
                    <canvas id="salaryTrendChart" aria-label="Salary trend line chart"></canvas>
                </div>
            </section>

            <section class="rounded-xl border border-slate-700 bg-slate-800 p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-slate-200 border-b border-slate-700 pb-2">Top companies hiring</h2>
                @if ($salaryData['top_companies']->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">No active listings for this stack and country yet.</p>
                @else
                    <ul class="mt-4 divide-y divide-slate-700">
                        @foreach ($salaryData['top_companies'] as $company)
                            <li class="flex flex-col gap-1 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                                <span class="font-medium text-slate-200">{{ $company->company }}</span>
                                <span class="text-slate-500">{{ $company->job_count }} {{ \Illuminate\Support\Str::plural('role', $company->job_count) }} · ~${{ number_format((int) $company->avg_mid_salary) }}/mo</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <aside class="space-y-4">
            <div class="rounded-xl border border-indigo-500/30 bg-slate-800 p-4 sm:p-5">
                <h2 class="text-base font-semibold text-slate-200">What does ${{ number_format($calculatorAmount) }}/mo become in PKR?</h2>
                <p class="mt-2 text-sm text-slate-400">Use our freelancer calculator to see take-home pay after Upwork fees and Payoneer withdrawal.</p>
                <a href="{{ route('calculator.index', ['amount' => $calculatorAmount, 'platform' => 'upwork', 'processor' => 'payoneer']) }}" class="mt-4 inline-block rounded-full bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-400">
                    Calculate take-home &rarr;
                </a>
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-800 p-4 sm:p-5">
                <h2 class="text-base font-semibold text-slate-200">Browse live jobs</h2>
                <a href="{{ route('jobs.index', ['stack' => $salaryData['stack'], 'country' => $salaryData['country']]) }}" class="mt-3 inline-block text-sm font-medium text-indigo-400 hover:underline">
                    View {{ $salaryData['stack'] }} jobs in {{ $salaryData['country'] }} &rarr;
                </a>
            </div>
        </aside>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('salaryTrendChart');
                if (!canvas || !window.Chart) {
                    return;
                }

                new window.Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: @json($salaryData['chart_labels']),
                        datasets: [{
                            label: 'Avg salary (USD/mo)',
                            data: @json($salaryData['chart_values']),
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.15)',
                            fill: true,
                            tension: 0.3,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            x: {
                                ticks: { color: '#94a3b8' },
                                grid: { color: '#334155' },
                            },
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    color: '#94a3b8',
                                    callback: (value) => '$' + value.toLocaleString(),
                                },
                                grid: { color: '#334155' },
                            },
                        },
                    },
                });
            });
        </script>
    @endpush
@endsection
