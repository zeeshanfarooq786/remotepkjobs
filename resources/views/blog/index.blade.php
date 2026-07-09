@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold" style="color: var(--color-text-primary);">Tech Blog</h1>
        <p class="mt-2" style="color: var(--color-text-secondary);">
            Daily curated insights on remote work, developer tools, freelancing, and salaries — written for developers in Pakistan and worldwide.
        </p>
    </div>

    @if ($posts->isEmpty())
        <div class="rounded-xl border border-dashed p-8 text-center" style="background: var(--color-bg-secondary); border-color: var(--color-card-border);">
            <p style="color: var(--color-text-secondary);">No blog posts yet. Run the blog generator agent to publish the first article.</p>
            <code class="mt-3 block text-xs" style="color: var(--color-text-muted);">python agents/blog_generator.py</code>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($posts as $post)
                <article class="card-accent rounded-xl border border-l-4 p-5 card" style="border-color: var(--color-card-border); border-left-color: var(--color-card-border-accent);">
                    <p class="text-xs" style="color: var(--color-text-muted);">
                        {{ $post->published_at?->format('M j, Y') }}
                        @if (! empty($post->tags))
                            &middot;
                            @foreach (array_slice($post->tags, 0, 3) as $tag)
                                <span class="tag-brand ml-1 inline rounded-full px-2 py-0.5 text-xs">{{ $tag }}</span>
                            @endforeach
                        @endif
                    </p>
                    <h2 class="mt-2 text-xl font-bold" style="color: var(--color-text-primary);">
                        <a href="{{ route('blog.show', $post->slug) }}" class="link-brand hover:underline">{{ $post->title }}</a>
                    </h2>
                    <p class="mt-2 text-sm leading-relaxed" style="color: var(--color-text-secondary);">{{ $post->excerpt }}</p>
                    <a href="{{ route('blog.show', $post->slug) }}" class="link-brand mt-3 inline-flex text-sm font-medium hover:underline">Read more &rarr;</a>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
