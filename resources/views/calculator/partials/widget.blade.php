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

<div
    x-data="createCalculatorComponent(
        window.DevRates.rates,
        window.DevRates.defaults,
        window.DevRates.logUrl,
        window.DevRates.csrfToken
    )"
    class="space-y-8"
>
    <section class="rounded-xl border p-4 sm:p-6 card" style="border-color: var(--color-card-border);">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <div>
                    <label for="amount" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Contract amount</label>
                    <input
                        id="amount"
                        type="number"
                        min="1"
                        max="500000"
                        step="0.01"
                        x-model.number="amount"
                        class="input-field h-9 w-full rounded-lg border px-3 text-base"
                    >
                </div>

                <div>
                    <label for="currency" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Currency</label>
                    <select
                        id="currency"
                        x-model="currency"
                        class="input-field h-9 w-full rounded-lg border px-3 text-base"
                    >
                        <option value="USD">USD — US Dollar</option>
                        <option value="GBP">GBP — British Pound</option>
                        <option value="EUR">EUR — Euro</option>
                    </select>
                </div>

                <div>
                    <label for="platform" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Freelance platform</label>
                    <select
                        id="platform"
                        x-model="platform"
                        class="input-field h-9 w-full rounded-lg border px-3 text-base"
                    >
                        <option value="upwork">Upwork (10% flat)</option>
                        <option value="fiverr">Fiverr (20% flat)</option>
                        <option value="toptal">Toptal (0% — client pays)</option>
                    </select>
                </div>

                <div>
                    <label for="processor" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Payout processor</label>
                    <select
                        id="processor"
                        x-model="processor"
                        class="input-field h-9 w-full rounded-lg border px-3 text-base"
                    >
                        <option value="payoneer">Payoneer (~2% withdrawal)</option>
                        <option value="wise">Wise (~0.5% transfer)</option>
                        <option value="direct">Direct local bank (HBL flat fee)</option>
                    </select>
                </div>
            </div>

            <div class="rounded-xl border p-4" style="background: var(--color-bg-secondary); border-color: var(--color-card-border);">
                <div class="mb-4">
                    <label for="output_currency" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Show result in:</label>
                    <select
                        id="output_currency"
                        x-model="outputCurrency"
                        class="input-field h-9 w-full rounded-lg border px-3 text-sm"
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
                <h2 class="mb-4 border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">Take-home summary</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt style="color: var(--color-text-secondary);">Gross (USD equivalent)</dt>
                        <dd class="font-medium" style="color: var(--color-text-primary);" x-text="formatMoney(results.gross_usd)"></dd>
                    </div>
                    <div class="flex justify-between" style="color: var(--color-danger);">
                        <dt>Platform fee</dt>
                        <dd x-text="'-' + formatMoney(results.platform_fee)"></dd>
                    </div>
                    <div class="flex justify-between" style="color: var(--color-warning);">
                        <dt>Processor fee</dt>
                        <dd x-text="'-' + formatMoney(results.processor_fee)"></dd>
                    </div>
                    <div class="flex justify-between" style="color: var(--color-warning);">
                        <dt>Bank fee</dt>
                        <dd x-text="'-' + formatMoney(results.bank_fee)"></dd>
                    </div>
                    <div class="border-t pt-3" style="border-color: var(--color-card-border);">
                        <p class="text-xs font-medium uppercase tracking-wide" style="color: var(--color-text-muted);">Net take-home</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--color-salary);" x-text="netTakeHomeLine"></p>
                        <p class="mt-2 text-xs" style="color: var(--color-text-muted);" x-show="outputCurrency !== 'USD'" x-text="outputCurrencyFlag + ' rate: 1 USD = ' + currentOutputRate + ' ' + outputCurrency"></p>
                    </div>
                </dl>
                <p class="mt-3 text-xs" style="color: var(--color-text-muted);" x-show="rates.rates_updated_at">
                    Exchange rates updated: <span x-text="formatRatesUpdated(rates.rates_updated_at)"></span>
                </p>
            </div>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border p-4 sm:p-6 card" style="border-color: var(--color-card-border);">
            <h2 class="mb-4 border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">Fee breakdown chart</h2>
            <div class="mx-auto max-w-xs">
                <canvas x-ref="pieChart" aria-label="Fee breakdown pie chart"></canvas>
            </div>
        </div>

        <div class="rounded-xl border p-4 sm:p-6 card" style="border-color: var(--color-card-border);">
            <h2 class="mb-4 border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">Detailed breakdown</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--color-card-border); color: var(--color-text-secondary);">
                            <th class="py-2 pr-4">Line item</th>
                            <th class="py-2 pr-4">USD</th>
                            <th class="py-2">PKR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, index) in results.breakdown" :key="index">
                            <tr class="border-b" style="border-color: var(--color-bg-tertiary);" :style="line.type === 'total' ? 'color: var(--color-salary); font-weight: 600;' : 'color: var(--color-text-secondary);'">
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

<section class="mt-10 rounded-xl border p-4 sm:p-6 card" style="background: var(--color-bg-secondary); border-color: var(--color-card-border);">
    <h2 class="border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">How this calculator works</h2>
    @if ($footerPage)
        <div class="mt-4 space-y-3 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
            @foreach (($footerPage->content['paragraphs'] ?? []) as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    @else
        <p class="mt-4 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
            Enter your contract amount, select your platform and payout method, and see your estimated take-home in USD and PKR.
            All math runs in your browser using live rates loaded once from our database.
        </p>
    @endif
</section>

<section class="mt-10 rounded-xl border p-4 sm:p-6 card" style="border-color: var(--color-card-border);">
    <h2 class="mb-4 border-b pb-2 text-lg font-semibold" style="color: var(--color-text-primary); border-color: var(--color-card-border);">Frequently asked questions</h2>
    <div class="space-y-4">
        @foreach ($faqItems as $item)
            <details class="rounded-lg border p-4" style="background: var(--color-bg-secondary); border-color: var(--color-card-border);">
                <summary class="cursor-pointer font-medium" style="color: var(--color-text-primary);">{{ $item['question'] }}</summary>
                <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">{{ $item['answer'] }}</p>
            </details>
        @endforeach
    </div>
</section>

@push('head')
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
