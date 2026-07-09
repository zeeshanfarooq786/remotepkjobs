@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold" style="color: var(--color-text-primary);">Privacy Policy</h1>
    <p class="mt-2 text-sm" style="color: var(--color-text-muted);">Last updated: {{ now()->format('F j, Y') }}</p>

    <div class="mt-6 space-y-6 rounded-xl border p-6 sm:p-8 card" style="border-color: var(--color-card-border);">
        <section>
            <h2 class="text-lg font-semibold" style="color: var(--color-text-primary);">Overview</h2>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                DevRates ("we", "us", "our") operates devrates.com. This Privacy Policy explains what information we collect, how we use it, and your rights regarding that data. By using our website, you agree to the practices described below.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold" style="color: var(--color-text-primary);">Information we collect</h2>
            <ul class="mt-2 list-disc space-y-2 pl-5 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                <li><strong style="color: var(--color-text-primary);">Blog content:</strong> Some articles are auto-curated from public developer communities (e.g. Hacker News, dev.to), rewritten with original commentary. Source links are provided where available.</li>
                <li><strong style="color: var(--color-text-primary);">Server logs:</strong> Standard web server logs (IP address, browser type, pages visited, timestamps) are retained temporarily for security and debugging.</li>
                <li><strong style="color: var(--color-text-primary);">Cookies:</strong> We use essential cookies for session management and CSRF protection. Third-party cookies may be set by advertising partners as described below.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold" style="color: var(--color-text-primary);">Google AdSense</h2>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                DevRates uses Google AdSense to display advertisements. Google and its partners may use cookies (including the DoubleClick cookie) to serve ads based on your prior visits to this site and other websites. These cookies enable Google and its partners to serve personalized ads.
            </p>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                You may opt out of personalized advertising by visiting
                <a href="https://www.google.com/settings/ads" class="link-brand hover:underline" rel="noopener noreferrer" target="_blank">Google Ads Settings</a>
                or
                <a href="https://optout.aboutads.info/" class="link-brand hover:underline" rel="noopener noreferrer" target="_blank">aboutads.info</a>.
                Third-party vendors, including Google, use cookies to serve ads on DevRates. Google's use of advertising cookies enables it and its partners to serve ads to users based on their visit to DevRates and/or other sites on the Internet.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold" style="color: var(--color-text-primary);">How we use your information</h2>
            <ul class="mt-2 list-disc space-y-2 pl-5 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                <li>To operate and improve our job listings, calculators, and tool directory.</li>
                <li>To monitor site performance, detect abuse, and prevent spam.</li>
                <li>To comply with legal obligations and respond to lawful requests.</li>
            </ul>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                We do not sell, rent, or trade your personal information to third parties.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold" style="color: var(--color-text-primary);">Third-party services</h2>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                Job listings link to external apply URLs operated by third parties. Our calculator uses publicly available exchange rate APIs. GitHub statistics are fetched from the GitHub API. Each third-party service has its own privacy policy governing data they collect when you visit their sites.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold" style="color: var(--color-text-primary);">Data retention</h2>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                Server logs are retained for up to 90 days. Anonymous calculator analytics may be retained indefinitely in aggregate form. You may request deletion of any personal data we hold by contacting us at
                <a href="mailto:hello@devrates.com" class="link-brand hover:underline">hello@devrates.com</a>.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold" style="color: var(--color-text-primary);">Children's privacy</h2>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                DevRates is not directed at children under 13. We do not knowingly collect personal information from children. If you believe a child has provided us with personal data, please contact us and we will delete it promptly.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold" style="color: var(--color-text-primary);">Changes to this policy</h2>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated revision date. Continued use of DevRates after changes constitutes acceptance of the revised policy.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold" style="color: var(--color-text-primary);">Contact</h2>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                For privacy-related questions, contact us at
                <a href="mailto:hello@devrates.com" class="link-brand hover:underline">hello@devrates.com</a>
                or visit our <a href="{{ route('contact') }}" class="link-brand hover:underline">contact page</a>.
            </p>
        </section>
    </div>
@endsection
