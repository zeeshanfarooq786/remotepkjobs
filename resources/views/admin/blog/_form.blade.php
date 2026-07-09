@php
    $isEdit = isset($post);
    $tagsValue = old('tags', $isEdit && ! empty($post->tags) ? implode(', ', $post->tags) : '');
    $publishedValue = old(
        'published_at',
        $isEdit && $post->published_at
            ? $post->published_at->format('Y-m-d\TH:i')
            : ''
    );
@endphp

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
        <div>
            <label for="title" class="admin-label mb-1 block text-sm font-medium">Title</label>
            <input type="text" id="title" name="title" value="{{ old('title', $isEdit ? $post->title : '') }}" required class="admin-input">
        </div>

        <div>
            <label for="excerpt" class="admin-label mb-1 block text-sm font-medium">Excerpt</label>
            <textarea id="excerpt" name="excerpt" rows="3" required class="admin-input">{{ old('excerpt', $isEdit ? $post->excerpt : '') }}</textarea>
        </div>

        <div>
            <label for="body" class="admin-label mb-1 block text-sm font-medium">Body (Markdown)</label>
            <textarea id="body" name="body" rows="18" required class="admin-input font-mono text-sm">{{ old('body', $isEdit ? $post->body : '') }}</textarea>
            <p class="admin-hint mt-1 text-xs">Use ## for headings. Supports Markdown links.</p>
        </div>
    </div>

    <div class="space-y-4">
        <div class="admin-card p-4">
            <h2 class="admin-heading text-sm font-semibold">Publish</h2>
            <div class="mt-3">
                <label for="published_at" class="admin-label mb-1 block text-sm font-medium">Published at</label>
                <input type="datetime-local" id="published_at" name="published_at" value="{{ $publishedValue }}" class="admin-input text-sm">
                <p class="admin-hint mt-1 text-xs">Leave empty to save as draft.</p>
            </div>
        </div>

        <div class="admin-card p-4">
            <h2 class="admin-heading text-sm font-semibold">SEO &amp; meta</h2>
            <div class="mt-3 space-y-3">
                <div>
                    <label for="meta_description" class="admin-label mb-1 block text-sm font-medium">Meta description</label>
                    <textarea id="meta_description" name="meta_description" rows="3" class="admin-input text-sm">{{ old('meta_description', $isEdit ? $post->meta_description : '') }}</textarea>
                </div>
                <div>
                    <label for="tags" class="admin-label mb-1 block text-sm font-medium">Tags</label>
                    <input type="text" id="tags" name="tags" value="{{ $tagsValue }}" placeholder="remote, laravel, upwork" class="admin-input text-sm">
                    <p class="admin-hint mt-1 text-xs">Comma-separated.</p>
                </div>
            </div>
        </div>

        <div
            class="admin-card p-4"
            x-data="heroImageUploader(@js($isEdit && ! empty($post->hero_image_url) ? $post->hero_image_url : null))"
        >
            <h2 class="admin-heading text-sm font-semibold">Hero image</h2>
            <div class="mt-3 space-y-3">
                <div x-show="preview" x-cloak class="overflow-hidden rounded-lg ring-1 ring-[var(--admin-border)]">
                    <img :src="preview" alt="Hero image preview" class="h-48 w-full object-cover">
                    <div class="border-t px-3 py-2 text-xs" style="border-color: var(--admin-border); background: var(--admin-bg-elevated);">
                        <p class="admin-heading font-medium" x-text="fileName || 'Current hero image'"></p>
                        <p class="admin-hint mt-0.5" x-show="fileSize" x-text="fileSize"></p>
                    </div>
                </div>

                <div>
                    <label for="hero_image" class="admin-label mb-1 block text-sm font-medium">Upload from PC</label>
                    <input
                        type="file"
                        id="hero_image"
                        name="hero_image"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        class="admin-input text-sm"
                        @change="onFileChange($event)"
                    >
                    <p class="admin-hint mt-1 text-xs">JPG, PNG, WebP, or GIF. Max 5 MB. Recommended 1640×720 (Facebook cover).</p>
                </div>

                <div>
                    <label for="hero_image_url" class="admin-label mb-1 block text-sm font-medium">Or paste image URL</label>
                    <input type="url" id="hero_image_url" name="hero_image_url" value="{{ old('hero_image_url', $isEdit ? $post->hero_image_url : '') }}" class="admin-input text-sm">
                    <p class="admin-hint mt-1 text-xs">Upload takes priority over URL if both are set.</p>
                </div>

                @if ($isEdit && ! empty($post->hero_image_url))
                    <label class="admin-hint flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_hero_image" value="1" class="rounded border-[var(--admin-border)] bg-[var(--admin-bg-input)]">
                        Remove current hero image
                    </label>
                @endif
            </div>
        </div>

        <div class="admin-card p-4">
            <h2 class="admin-heading text-sm font-semibold">Source (optional)</h2>
            <div class="mt-3 space-y-3">
                <div>
                    <label for="source_name" class="admin-label mb-1 block text-sm font-medium">Source name</label>
                    <input type="text" id="source_name" name="source_name" value="{{ old('source_name', $isEdit ? $post->source_name : '') }}" class="admin-input text-sm">
                </div>
                <div>
                    <label for="source_url" class="admin-label mb-1 block text-sm font-medium">Source URL</label>
                    <input type="url" id="source_url" name="source_url" value="{{ old('source_url', $isEdit ? $post->source_url : '') }}" class="admin-input text-sm">
                </div>
            </div>
        </div>

        @if ($isEdit)
            <div class="admin-card admin-meta-box p-4 text-xs">
                <p><strong>Slug:</strong> {{ $post->slug }}</p>
                <p class="mt-1"><strong>Topic key:</strong> {{ Str::limit($post->topic_key, 20) }}…</p>
                @if ($post->published_at && $post->published_at->isPast())
                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="link-brand mt-2 inline-block font-medium">View live post →</a>
                @endif
            </div>
        @endif
    </div>
</div>
