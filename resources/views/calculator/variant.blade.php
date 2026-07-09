@extends('layouts.app')

@php
    $variantDefaults = array_merge(
        [
            'amount' => 1000,
            'platform' => 'upwork',
            'processor' => 'payoneer',
            'currency' => 'USD',
        ],
        $page->content['defaults'] ?? []
    );
@endphp

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-100">{{ $heading }}</h1>
        <p class="mt-2 text-slate-300">
            {{ $page->content['intro'] ?? 'Pre-configured calculator for Pakistani freelancers. Adjust any field to compare scenarios.' }}
        </p>
    </div>

    <script>
        window.DevRates = {
            rates: @json($rates),
            defaults: @json($variantDefaults),
            logUrl: @json(route('calculator.calculate')),
            csrfToken: @json(csrf_token()),
        };
    </script>

    <div
        x-data="createCalculatorComponent(
            window.DevRates.rates,
            window.DevRates.defaults,
            window.DevRates.logUrl,
            window.DevRates.csrfToken
        )"
        x-init="
            amount = Number({{ json_encode($variantDefaults['amount']) }});
            platform = {{ json_encode($variantDefaults['platform']) }};
            processor = {{ json_encode($variantDefaults['processor']) }};
            currency = {{ json_encode($variantDefaults['currency']) }};
            $nextTick(() => {
                renderChart();
                scheduleLog();
            });
        "
        class="space-y-8"
    >
        <section class="rounded-xl border border-slate-700 bg-slate-800 p-4 sm:p-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <label for="amount" class="mb-1 block text-xs uppercase tracking-wide text-slate-500">Contract amount</label>
                        <input
                            id="amount"
                            type="number"
                            min="1"
                            max="500000"
                            step="0.01"
                            x-model.number="amount"
                            class="h-9 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 text-base text-slate-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label for="currency" class="mb-1 block text-xs uppercase tracking-wide text-slate-500">Currency</label>
                        <select
                            id="currency"
                            x-model="currency"
                            class="h-9 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 text-base text-slate-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="USD">USD — US Dollar</option>
                            <option value="GBP">GBP — British Pound</option>
                            <option value="EUR">EUR — Euro</option>
                        </select>
                    </div>

                    <div>
                        <label for="platform" class="mb-1 block text-xs uppercase tracking-wide text-slate-500">Freelance platform</label>
                        <select
                            id="platform"
                            x-model="platform"
                            class="h-9 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 text-base text-slate-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="upwork">Upwork (10% flat)</option>
                            <option value="fiverr">Fiverr (20% flat)</option>
                            <option value="toptal">Toptal (0% — client pays)</option>
                        </select>
                    </div>

                    <div>
                        <label for="processor" class="mb-1 block text-xs uppercase tracking-wide text-slate-500">Payout processor</label>
                        <select
                            id="processor"
                            x-model="processor"
                            class="h-9 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 text-base text-slate-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="payoneer">Payoneer (~2% withdrawal)</option>
                            <option value="wise">Wise (~0.5% transfer)</option>
                            <option value="direct">Direct local bank (HBL flat fee)</option>
                        </select>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-700 bg-slate-900 p-4">
                    <div class="mb-4">
                        <label for="output_currency" class="mb-1 block text-xs uppercase tracking-wide text-slate-500">Show result in:</label>
                        <select
                            id="output_currency"
                            x-model="outputCurrency"
                            class="h-9 w-full rounded-lg border border-slate-700 bg-slate-800 px-3 text-sm text-slate-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="USD">🇺🇸 USD — US Dollar</option>
                            <option value="PKR">🇵🇰 PKR — Pakistani Rupee</option>
                            <option value="GBP">🇬🇧 GBP — British Pound</option>
                            <option value="EUR">🇪🇺 EUR — Euro</option>
                            <option value="CAD">🇨🇦 CAD — Canadian Dollar</option>
                            <option value="AUD">🇦🇺 AUD — Australian Dollar</option>
                            <option value="AED">🇦🇪 AED — UAE Dirham</option>
                            <option value="SAR">🇸🇦 SAR — Saudi Riyal</option>
                            <option value="BDT">🇧🇩 BDT — Bangladeshi Taka</option>
                            <option value="INR">🇮🇳 INR — Indian Rupee</option>
                            <option value="NGN">🇳🇬 NGN — Nigerian Naira</option>
                        </select>
                    </div>
                    <h2 class="mb-4 text-lg font-semibold text-slate-200 border-b border-slate-700 pb-2">Take-home summary</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Gross (USD equivalent)</dt>
                            <dd class="font-medium text-slate-200" x-text="formatMoney(results.gross_usd)"></dd>
                        </div>
                        <div class="flex justify-between text-red-400">
                            <dt>Platform fee</dt>
                            <dd x-text="'-' + formatMoney(results.platform_fee)"></dd>
                        </div>
                        <div class="flex justify-between text-orange-400">
                            <dt>Processor fee</dt>
                            <dd x-text="'-' + formatMoney(results.processor_fee)"></dd>
                        </div>
                        <div class="flex justify-between text-yellow-400">
                            <dt>Bank fee</dt>
                            <dd x-text="'-' + formatMoney(results.bank_fee)"></dd>
                        </div>
                        <div class="border-t border-slate-700 pt-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Net take-home</p>
                            <p class="mt-2 text-sm font-semibold text-emerald-500" x-text="netTakeHomeLine"></p>
                            <p class="mt-2 text-xs text-slate-500" x-show="outputCurrency !== 'USD'" x-text="outputCurrencyFlag + ' rate: 1 USD = ' + currentOutputRate + ' ' + outputCurrency"></p>
                        </div>
                    </dl>
                    <p class="mt-3 text-xs text-slate-500" x-show="rates.rates_updated_at">
                        Exchange rates updated: <span x-text="formatRatesUpdated(rates.rates_updated_at)"></span>
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-700 bg-slate-800 p-4 sm:p-6">
                <h2 class="mb-4 text-lg font-semibold text-slate-200 border-b border-slate-700 pb-2">Fee breakdown chart</h2>
                <div class="mx-auto max-w-xs">
                    <canvas x-ref="pieChart" aria-label="Fee breakdown pie chart"></canvas>
                </div>
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-800 p-4 sm:p-6">
                <h2 class="mb-4 text-lg font-semibold text-slate-200 border-b border-slate-700 pb-2">Detailed breakdown</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-700 text-slate-400">
                                <th class="py-2 pr-4">Line item</th>
                                <th class="py-2 pr-4">USD</th>
                                <th class="py-2">PKR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(line, index) in results.breakdown" :key="index">
                                <tr class="border-b border-slate-700/50" :class="line.type === 'total' ? 'font-semibold text-emerald-500' : 'text-slate-300'">
                                    <td class="py-2 pr-4" x-text="line.label"></td>
                                    <td class="py-2 pr-4" x-text="(line.usd < 0 ? '-' : '') + formatMoney(line.usd)"></td>
                                    <td class="py-2" x-text="(line.pkr < 0 ? '-' : '') + formatMoney(line.pkr, 'PKR')"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    @php
        $faqItems = [
            [
                'question' => 'How much does Upwork charge freelancers?',
                'answer' => 'Upwork charges a flat 10% service fee on all freelancer earnings. The old tiered system (20% / 10% / 5%) was replaced with a single flat 10% rate.',
            ],
            [
                'question' => 'What are Payoneer withdrawal fees?',
                'answer' => 'Payoneer typically charges around 2% on withdrawals to local bank accounts. This calculator applies the configured rate and converts your remaining balance to your chosen currency.',
            ],
            [
                'question' => 'Is Wise cheaper than Payoneer?',
                'answer' => 'Wise usually charges about 0.5% on transfers, which is lower than Payoneer\'s ~2% withdrawal fee. Compare both options above to see your exact take-home in any currency.',
            ],
        ];
    @endphp

    <section class="mt-10 rounded-xl border border-slate-700 bg-slate-800 p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-slate-200 border-b border-slate-700 pb-2">How this calculator works</h2>
        @if ($footerPage)
            <div class="mt-4 space-y-3 text-sm leading-relaxed text-slate-300">
                @foreach (($footerPage->content['paragraphs'] ?? []) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        @else
            <p class="mt-4 text-sm leading-relaxed text-slate-300">
                Enter your contract amount, select your platform and payout method, and see your estimated take-home in USD and PKR.
                All math runs in your browser using live rates loaded once from our database.
            </p>
        @endif
    </section>

    <section class="mt-10 rounded-xl border border-slate-700 bg-slate-800 p-4 sm:p-6">
        <h2 class="mb-4 text-lg font-semibold text-slate-200 border-b border-slate-700 pb-2">Frequently asked questions</h2>
        <div class="space-y-4">
            @foreach ($faqItems as $item)
                <details class="rounded-lg border border-slate-700 bg-slate-900 p-4">
                    <summary class="cursor-pointer font-medium text-slate-200">{{ $item['question'] }}</summary>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $item['answer'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    @push('schema')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect($faqItems)->map(fn ($item) => [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ])->values()->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @endpush
@endsection
