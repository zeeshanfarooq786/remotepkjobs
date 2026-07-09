<style>[x-cloak] { display: none !important; }</style>

<header
    x-data="{ mobileOpen: false }"
    class="sticky top-0 z-50 border-b"
    style="background: var(--color-nav-bg); border-color: var(--color-nav-border);"
>
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
        <a href="/" class="logo-text" style="
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            text-decoration: none;
            color: var(--color-brand);
        ">remote<span style="color: var(--color-text-primary);">pk</span><span style="color: var(--color-brand);">jobs</span></a>

        <nav class="hidden items-center gap-5 text-sm font-medium md:flex">
            <a
                href="{{ route('jobs.index') }}"
                class="{{ request()->routeIs('jobs.*') ? 'nav-link-active' : 'nav-link' }}"
            >
                Jobs
            </a>
            <a
                href="{{ route('calculator.index') }}"
                class="{{ request()->routeIs('calculator.*') ? 'nav-link-active' : 'nav-link' }}"
            >
                Calculator
            </a>
            <a
                href="{{ route('tools.index') }}"
                class="{{ request()->routeIs('tools.*') ? 'nav-link-active' : 'nav-link' }}"
            >
                Self-Hosted Tools
            </a>
            <a
                href="{{ route('blog.index') }}"
                class="{{ request()->routeIs('blog.*') ? 'nav-link-active' : 'nav-link' }}"
            >
                Blog
            </a>
        </nav>

        <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg border p-2 md:hidden"
            style="border-color: var(--color-card-border); color: var(--color-nav-text);"
            @click="mobileOpen = !mobileOpen"
            :aria-expanded="mobileOpen"
            aria-label="Toggle navigation menu"
        >
            <svg x-show="!mobileOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="mobileOpen" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav
        x-show="mobileOpen"
        x-cloak
        x-transition
        class="border-t md:hidden"
        style="background: var(--color-nav-bg); border-color: var(--color-nav-border);"
    >
        <div class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-3 text-sm font-medium">
            <a
                href="{{ route('jobs.index') }}"
                class="rounded-lg px-3 py-2"
                style="{{ request()->routeIs('jobs.*') ? 'background: var(--color-brand-light); color: var(--color-brand);' : 'color: var(--color-text-secondary);' }}"
                @click="mobileOpen = false"
            >
                Jobs
            </a>
            <a
                href="{{ route('calculator.index') }}"
                class="rounded-lg px-3 py-2"
                style="{{ request()->routeIs('calculator.*') ? 'background: var(--color-brand-light); color: var(--color-brand);' : 'color: var(--color-text-secondary);' }}"
                @click="mobileOpen = false"
            >
                Calculator
            </a>
            <a
                href="{{ route('tools.index') }}"
                class="rounded-lg px-3 py-2"
                style="{{ request()->routeIs('tools.*') ? 'background: var(--color-brand-light); color: var(--color-brand);' : 'color: var(--color-text-secondary);' }}"
                @click="mobileOpen = false"
            >
                Self-Hosted Tools
            </a>
            <a
                href="{{ route('blog.index') }}"
                class="rounded-lg px-3 py-2"
                style="{{ request()->routeIs('blog.*') ? 'background: var(--color-brand-light); color: var(--color-brand);' : 'color: var(--color-text-secondary);' }}"
                @click="mobileOpen = false"
            >
                Blog
            </a>
        </div>
    </nav>
</header>
