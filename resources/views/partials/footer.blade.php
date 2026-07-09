<footer class="border-t" style="background: var(--color-footer-bg); border-color: var(--color-nav-border);">
    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <p class="text-sm font-semibold" style="color: var(--color-text-primary);">remote<span style="color: var(--color-text-primary);">pk</span><span style="color: var(--color-brand);">jobs</span></p>
                <p class="mt-2 text-sm" style="color: var(--color-footer-text);">Remote jobs, payout calculators, and self-hosted alternatives for freelance developers worldwide.</p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--color-text-muted);">Company</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('about') }}" class="link-brand" style="color: var(--color-footer-text);">About</a></li>
                    <li><a href="{{ route('contact') }}" class="link-brand" style="color: var(--color-footer-text);">Contact</a></li>
                    <li><a href="{{ route('privacy') }}" class="link-brand" style="color: var(--color-footer-text);">Privacy Policy</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--color-text-muted);">Tools</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('calculator.index') }}" class="link-brand" style="color: var(--color-footer-text);">Calculator</a></li>
                    <li><a href="{{ route('jobs.index') }}" class="link-brand" style="color: var(--color-footer-text);">Jobs</a></li>
                    <li><a href="{{ route('tools.index') }}" class="link-brand" style="color: var(--color-footer-text);">Tools</a></li>
                    <li><a href="{{ route('blog.index') }}" class="link-brand" style="color: var(--color-footer-text);">Blog</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-8 border-t pt-6 text-center text-sm" style="border-color: var(--color-bg-tertiary); color: var(--color-text-muted);">
            <p>
                Data updated daily
                @if (! empty($lastDataUpdated) && $lastDataUpdated->greaterThan(now()->subHours(48)))
                    &middot; Last sync {{ $lastDataUpdated->diffForHumans() }}
                @else
                    &middot; Updated regularly
                @endif
            </p>
            <p class="mt-2">&copy; {{ date('Y') }} remotepkjobs &middot; Built for freelance developers worldwide</p>
        </div>
    </div>

    <!-- ADSENSE_FOOTER -->
</footer>
