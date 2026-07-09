<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Support\Str;

class BlogIngestionService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{status: string, post?: BlogPost}
     */
    public function storePost(array $data): array
    {
        if (BlogPost::query()->where('topic_key', $data['topic_key'])->exists()) {
            return ['status' => 'duplicate'];
        }

        $normalizedTitle = $this->normalizeTitle($data['title']);
        $titleExists = BlogPost::query()
            ->get(['title'])
            ->contains(fn ($post) => $this->normalizeTitle($post->title) === $normalizedTitle);

        if ($titleExists) {
            return ['status' => 'duplicate'];
        }

        $slug = $this->generateUniqueSlug($data['title']);

        $post = BlogPost::query()->create([
            'title' => $data['title'],
            'slug' => $slug,
            'topic_key' => $data['topic_key'],
            'excerpt' => $data['excerpt'],
            'body' => $data['body'],
            'hero_image_url' => $data['hero_image_url'] ?? null,
            'meta_description' => $data['meta_description'] ?? Str::limit($data['excerpt'], 155),
            'source_url' => $data['source_url'] ?? null,
            'source_name' => $data['source_name'] ?? null,
            'tags' => $data['tags'] ?? [],
            'published_at' => $data['published_at'] ?? now(),
        ]);

        return [
            'status' => 'created',
            'post' => $post,
        ];
    }

    /**
     * @return list<string>
     */
    public function existingTopicKeys(): array
    {
        return BlogPost::query()
            ->orderByDesc('id')
            ->limit(1000)
            ->pluck('topic_key')
            ->all();
    }

    private function generateUniqueSlug(string $title): string
    {
        $base = Str::slug(Str::limit($title, 80, ''));
        $slug = $base;
        $suffix = 2;

        while (BlogPost::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function normalizeTitle(string $title): string
    {
        $cleaned = preg_replace('/[^\w\s]/u', ' ', mb_strtolower($title));
        $cleaned = preg_replace('/\s+/', ' ', trim((string) $cleaned));

        return $cleaned;
    }
}
