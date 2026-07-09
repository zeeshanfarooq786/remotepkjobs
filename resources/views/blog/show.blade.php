@extends('layouts.app')

@section('content')
    <article class="mx-auto max-w-3xl">
        <header class="mb-6">
            <p class="text-sm" style="color: var(--color-text-muted);">
                {{ $post->published_at?->format('F j, Y') }}
            </p>
            <h1 class="mt-2 text-3xl font-bold leading-tight sm:text-4xl" style="color: var(--color-text-primary);">{{ $post->title }}</h1>
            @if (! empty($post->tags))
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <span class="tag-brand rounded-full px-2.5 py-1 text-xs font-medium">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </header>

        @if (! empty($post->hero_image_url))
            <img
                src="{{ $post->hero_image_url }}"
                alt="{{ $post->title }}"
                class="mb-6 h-64 w-full rounded-lg object-cover"
                loading="lazy"
                width="800"
                height="400"
            >
        @endif

        <div
            class="rounded-xl border bg-white p-6 sm:p-10"
            style="border-color: var(--color-card-border); background: var(--color-card-bg); box-shadow: var(--color-card-shadow);"
        >
            <div class="prose prose-slate max-w-none prose-headings:text-[var(--color-text-primary)] prose-p:text-[var(--color-text-secondary)] prose-a:text-[var(--color-brand)] prose-strong:text-[var(--color-text-primary)]">
                {!! \Illuminate\Support\Str::markdown($post->body) !!}
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-4 border-t pt-6" style="border-color: var(--color-card-border);">
            <a href="{{ route('blog.index') }}" class="link-brand text-sm font-medium hover:underline">&larr; All posts</a>
            <a href="{{ route('jobs.index') }}" class="link-brand text-sm font-medium hover:underline">Browse jobs</a>
            <a href="{{ route('calculator.index') }}" class="link-brand text-sm font-medium hover:underline">Fee calculator</a>
        </div>
    </article>

    @push('schema')
        <script type="application/ld+json">
            {!! json_encode(array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $post->title,
                'description' => $post->excerpt,
                'datePublished' => $post->published_at?->toIso8601String(),
                'dateModified' => $post->updated_at?->toIso8601String(),
                'image' => $post->hero_image_url,
                'author' => [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                ],
                'mainEntityOfPage' => route('blog.show', $post->slug),
            ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @endpush
@endsection
