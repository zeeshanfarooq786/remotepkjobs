<div
    x-data="createCalculatorComponent(
        window.DevRates.rates,
        window.DevRates.defaults,
        null,
        null
    )"
    class="rounded-xl border p-4 sm:p-5 card"
    style="background: var(--color-brand-light); border-color: var(--color-brand);"
>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="mini_amount" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Contract amount (USD)</label>
            <input
                id="mini_amount"
                type="number"
                min="1"
                max="500000"
                step="0.01"
                x-model.number="amount"
                class="input-field h-9 w-full rounded-lg border px-3 text-base"
            >
        </div>

        <div class="flex-1">
            <label for="mini_platform" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Platform</label>
            <select
                id="mini_platform"
                x-model="platform"
                class="input-field h-9 w-full rounded-lg border px-3 text-base"
            >
                <option value="upwork">Upwork (10% flat)</option>
                <option value="fiverr">Fiverr (20% flat)</option>
                <option value="toptal">Toptal (0% — client pays)</option>
            </select>
        </div>
    </div>

    <p class="mt-4 text-sm font-semibold" style="color: var(--color-salary);" x-text="netTakeHomeLine"></p>

    <p class="mt-2 text-xs" style="color: var(--color-text-muted);">
        Includes platform + Payoneer fees.
        <a href="{{ route('calculator.index') }}" class="link-brand hover:underline">Full calculator &rarr;</a>
    </p>
</div>
