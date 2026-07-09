@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold" style="color: var(--color-text-primary);">Contact</h1>
    <p class="mt-2" style="color: var(--color-text-secondary);">Questions, corrections, partnerships, or press inquiries — we read every message.</p>

    <div class="mt-6 max-w-xl rounded-xl border p-6 sm:p-8 card" style="border-color: var(--color-card-border);">
        <form
            class="space-y-4"
            onsubmit="event.preventDefault(); const s = document.getElementById('subject').value; const b = 'From: ' + document.getElementById('name').value + ' (' + document.getElementById('email').value + ')\n\n' + document.getElementById('message').value; window.location.href = 'mailto:hello@devrates.com?subject=' + encodeURIComponent(s) + '&body=' + encodeURIComponent(b);"
        >
            <div>
                <label for="name" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Your name</label>
                <input
                    type="text"
                    id="name"
                    required
                    placeholder="Jane Developer"
                    class="input-field h-9 w-full rounded-lg border px-3 text-sm placeholder:text-[var(--color-text-muted)]"
                >
            </div>

            <div>
                <label for="email" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Your email</label>
                <input
                    type="email"
                    id="email"
                    required
                    placeholder="you@example.com"
                    class="input-field h-9 w-full rounded-lg border px-3 text-sm placeholder:text-[var(--color-text-muted)]"
                >
            </div>

            <div>
                <label for="subject" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Subject</label>
                <input
                    type="text"
                    id="subject"
                    required
                    placeholder="Correction on Upwork fee rate"
                    class="input-field h-9 w-full rounded-lg border px-3 text-sm placeholder:text-[var(--color-text-muted)]"
                >
            </div>

            <div>
                <label for="message" class="mb-1 block text-xs uppercase tracking-wide" style="color: var(--color-text-muted);">Message</label>
                <textarea
                    id="message"
                    required
                    rows="5"
                    placeholder="Tell us what you need..."
                    class="input-field w-full rounded-lg border px-3 py-2 text-sm placeholder:text-[var(--color-text-muted)]"
                ></textarea>
            </div>

            <button
                type="submit"
                class="btn-primary inline-flex w-full items-center justify-center rounded-full px-6 py-3 text-sm font-semibold sm:w-auto"
            >
                Send via email
            </button>
        </form>

        <p class="mt-4 text-xs" style="color: var(--color-text-muted);">
            This opens your default email client with a pre-filled message to
            <a href="mailto:hello@devrates.com" class="link-brand hover:underline">hello@devrates.com</a>.
            No data is stored on our servers.
        </p>
    </div>
@endsection
