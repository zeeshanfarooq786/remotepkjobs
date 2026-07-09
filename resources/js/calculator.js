export function createCalculatorComponent(rates, defaults = {}, logUrl = null, csrfToken = null) {
    return {
        rates,
        amount: Number(defaults.amount ?? 1000),
        platform: defaults.platform ?? 'upwork',
        processor: defaults.processor ?? 'payoneer',
        currency: defaults.currency ?? 'USD',
        outputCurrency: 'USD',
        chart: null,
        logTimer: null,
        logUrl,
        csrfToken,

        outputCurrencyOptions: [
            { code: 'PKR', flag: '🇵🇰', label: 'PKR — Pakistani Rupee' },
            { code: 'USD', flag: '🇺🇸', label: 'USD — US Dollar' },
            { code: 'GBP', flag: '🇬🇧', label: 'GBP — British Pound' },
            { code: 'EUR', flag: '🇪🇺', label: 'EUR — Euro' },
            { code: 'CAD', flag: '🇨🇦', label: 'CAD — Canadian Dollar' },
            { code: 'AUD', flag: '🇦🇺', label: 'AUD — Australian Dollar' },
            { code: 'AED', flag: '🇦🇪', label: 'AED — UAE Dirham' },
            { code: 'SAR', flag: '🇸🇦', label: 'SAR — Saudi Riyal' },
            { code: 'BDT', flag: '🇧🇩', label: 'BDT — Bangladeshi Taka' },
            { code: 'INR', flag: '🇮🇳', label: 'INR — Indian Rupee' },
            { code: 'NGN', flag: '🇳🇬', label: 'NGN — Nigerian Naira' },
        ],

        init() {
            this.$nextTick(() => {
                this.renderChart();
            });

            this.$watch('results', () => {
                this.renderChart();
                this.scheduleLog();
            });
        },

        get results() {
            return this.compute();
        },

        get convertedNet() {
            const rate = this.getOutputRate(this.outputCurrency);
            return this.round(this.results.net_usd * rate);
        },

        get outputCurrencyFlag() {
            return this.outputCurrencyOptions.find((o) => o.code === this.outputCurrency)?.flag ?? '';
        },

        get currentOutputRate() {
            const rate = this.getOutputRate(this.outputCurrency);
            return this.outputCurrency === 'USD' ? 1 : this.round(rate);
        },

        get netTakeHomeLine() {
            const netUsd = this.formatMoney(this.results.net_usd);

            if (this.outputCurrency === 'USD') {
                return `Net take-home: ${netUsd} USD`;
            }

            return `Net take-home: ${netUsd} USD = ${this.formatOutputCurrency(this.convertedNet)}`;
        },

        getOutputRate(code) {
            if (code === 'USD') {
                return 1;
            }

            return Number(this.rates.exchange_rates?.[code] ?? 0);
        },

        compute() {
            const gross = Number(this.amount) || 0;
            const grossUsd = this.toUsd(gross, this.currency);
            const pkrRate = Number(this.rates.exchange_rates?.PKR ?? this.rates.usd_to_pkr) || 278;
            const platformPct = Number(this.rates.platforms?.[this.platform]?.fee_percent ?? 0);
            const platformFeeUsd = this.round(grossUsd * (platformPct / 100));
            const afterPlatformUsd = grossUsd - platformFeeUsd;

            const processorConfig = this.rates.processors?.[this.processor] ?? {};
            let processorFeeUsd = 0;

            if (processorConfig.fee_type === 'percent') {
                processorFeeUsd = this.round(afterPlatformUsd * (Number(processorConfig.fee_percent) / 100));
            }

            let bankFeeUsd = 0;
            if (this.processor === 'direct') {
                bankFeeUsd = Number(processorConfig.fee_flat_usd ?? 0);
            }

            const netUsd = this.round(Math.max(afterPlatformUsd - processorFeeUsd - bankFeeUsd, 0));
            const netPkr = this.round(netUsd * pkrRate);

            const breakdown = [
                {
                    label: 'Gross earnings',
                    usd: this.round(grossUsd),
                    pkr: this.round(grossUsd * pkrRate),
                    type: 'credit',
                },
                {
                    label: `${this.capitalize(this.platform)} platform fee (${platformPct}%)`,
                    usd: -platformFeeUsd,
                    pkr: -this.round(platformFeeUsd * pkrRate),
                    type: 'deduction',
                },
            ];

            if (processorFeeUsd > 0) {
                breakdown.push({
                    label: `${this.capitalize(this.processor)} transfer fee (${processorConfig.fee_percent}%)`,
                    usd: -processorFeeUsd,
                    pkr: -this.round(processorFeeUsd * pkrRate),
                    type: 'deduction',
                });
            }

            if (bankFeeUsd > 0) {
                breakdown.push({
                    label: 'Local bank withdrawal fee (HBL flat)',
                    usd: -bankFeeUsd,
                    pkr: -this.round(bankFeeUsd * pkrRate),
                    type: 'deduction',
                });
            }

            breakdown.push({
                label: 'You keep (net)',
                usd: netUsd,
                pkr: netPkr,
                type: 'total',
            });

            return {
                gross,
                gross_usd: this.round(grossUsd),
                platform_fee: platformFeeUsd,
                processor_fee: processorFeeUsd,
                bank_fee: bankFeeUsd,
                conversion_rate: pkrRate,
                net_usd: netUsd,
                net_pkr: netPkr,
                breakdown,
                chart: {
                    platform: platformFeeUsd,
                    processor: processorFeeUsd,
                    bank: bankFeeUsd,
                    keep: netUsd,
                },
            };
        },

        toUsd(amount, currency) {
            if (currency === 'USD') {
                return amount;
            }

            if (currency === 'EUR') {
                const rate = Number(this.rates.exchange_rates?.EUR ?? this.rates.usd_to_eur) || 0.92;
                return rate > 0 ? amount / rate : amount;
            }

            if (currency === 'GBP') {
                const rate = Number(this.rates.exchange_rates?.GBP ?? this.rates.usd_to_gbp) || 0.79;
                return rate > 0 ? amount / rate : amount;
            }

            return amount;
        },

        renderChart() {
            const canvas = this.$refs.pieChart;
            if (!canvas || !window.Chart) {
                return;
            }

            const data = this.results.chart;

            if (this.chart) {
                this.chart.destroy();
            }

            this.chart = new window.Chart(canvas, {
                type: 'pie',
                data: {
                    labels: ['Platform fee', 'Processor fee', 'Bank fee', 'You keep'],
                    datasets: [{
                        data: [data.platform, data.processor, data.bank, data.keep],
                        backgroundColor: ['#ef4444', '#f97316', '#eab308', '#22c55e'],
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#94a3b8' },
                        },
                    },
                },
            });
        },

        scheduleLog() {
            if (!this.logUrl || !this.csrfToken) {
                return;
            }

            clearTimeout(this.logTimer);
            this.logTimer = setTimeout(() => this.logCalculation(), 2000);
        },

        async logCalculation() {
            try {
                await fetch(this.logUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        amount: this.amount,
                        platform: this.platform,
                        processor: this.processor,
                        currency: this.currency,
                    }),
                });
            } catch (error) {
                console.debug('Calculator analytics log failed', error);
            }
        },

        formatMoney(value, currency = 'USD') {
            const abs = Math.abs(Number(value) || 0);
            const formatted = abs.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            if (currency === 'PKR') {
                return `Rs ${formatted}`;
            }

            return `$${formatted}`;
        },

        formatOutputCurrency(amount) {
            const symbols = {
                PKR: 'Rs ',
                USD: '$',
                GBP: '£',
                EUR: '€',
                CAD: 'C$',
                AUD: 'A$',
                AED: 'AED ',
                SAR: 'SAR ',
                BDT: '৳',
                INR: '₹',
                NGN: '₦',
            };

            const sym = symbols[this.outputCurrency] ?? '';
            const formatted = Math.abs(Number(amount) || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            return `${sym}${formatted} ${this.outputCurrency}`;
        },

        round(value) {
            return Math.round((Number(value) + Number.EPSILON) * 100) / 100;
        },

        capitalize(value) {
            return String(value).charAt(0).toUpperCase() + String(value).slice(1);
        },

        formatRatesUpdated(isoString) {
            if (!isoString) {
                return 'unknown';
            }

            const updated = new Date(isoString);
            const diffMs = Date.now() - updated.getTime();
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));

            if (diffHours < 1) {
                return 'less than 1 hour ago';
            }

            if (diffHours < 24) {
                return `${diffHours} hour${diffHours === 1 ? '' : 's'} ago`;
            }

            const diffDays = Math.floor(diffHours / 24);
            return `${diffDays} day${diffDays === 1 ? '' : 's'} ago`;
        },
    };
}
