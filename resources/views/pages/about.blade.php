@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold" style="color: var(--color-text-primary);">About DevRates</h1>

    <div class="mt-6 max-w-none rounded-xl border p-6 sm:p-8 card" style="border-color: var(--color-card-border);">
        <p class="leading-relaxed" style="color: var(--color-text-secondary);">
            DevRates is an independent resource for freelance developers who work remotely with international clients. We built this site because salary figures on job boards rarely tell the full story. A $5,000 monthly contract on Upwork is not $5,000 in your bank account — platform fees, withdrawal charges, and currency conversion all take a cut before you see a single deposit. Developers in Pakistan, India, Bangladesh, Nigeria, and dozens of other countries face the same puzzle every month: how much will I actually keep?
        </p>

        <p class="mt-4 leading-relaxed" style="color: var(--color-text-secondary);">
            Our job board aggregates remote roles from trusted sources and normalizes them into a single searchable feed. Each listing includes stack tags, salary ranges where disclosed, country restrictions, and direct apply links. We filter out spam, low-quality postings, and roles that are not genuinely remote. Salary snapshot data tracks average monthly pay by stack and country over time, giving you a benchmark before you negotiate your next contract.
        </p>

        <p class="mt-4 leading-relaxed" style="color: var(--color-text-secondary);">
            The freelancer fee calculator is the core tool on DevRates. Enter your contract amount, choose your platform — Upwork, Fiverr, or Toptal — and select how you withdraw: Payoneer, Wise, or a local bank. The calculator applies current platform fee percentages, processor withdrawal costs, and live exchange rates synced daily from public APIs. All math runs in your browser for instant results as you type. We also publish variant calculators for common scenarios, such as Upwork earnings converted to PKR or Fiverr payouts through Payoneer in Pakistan.
        </p>

        <p class="mt-4 leading-relaxed" style="color: var(--color-text-secondary);">
            Beyond jobs and payouts, DevRates maintains a curated directory of self-hosted alternatives to expensive SaaS tools. If you are paying for Pusher, Mailgun, Heroku, Datadog, or similar services every month, there is often an open-source replacement you can run on your own infrastructure. Each alternative entry includes GitHub star counts, Docker support, Laravel compatibility, estimated monthly savings, and a feature comparison against the paid tool. Stats refresh automatically via our GitHub updater agent.
        </p>

        <p class="mt-4 leading-relaxed" style="color: var(--color-text-secondary);">
            DevRates is operated by a small team of developers who have freelanced on Upwork and Fiverr for years. We are not affiliated with any platform, payment processor, or job board. Our exchange rates and fee percentages are sourced from public data and updated on a daily schedule. If you spot an error in our numbers or want to suggest a new tool alternative, please reach out through our contact page. We built DevRates to be the honest reference we wished existed when we started freelancing — and we keep improving it every week.
        </p>
    </div>
@endsection
