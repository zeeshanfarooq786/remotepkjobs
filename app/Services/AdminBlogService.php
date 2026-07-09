<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AdminBlogService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $heroImage = null): BlogPost
    {
        $slug = $this->generateUniqueSlug($data['title']);
        $topicKey = $data['topic_key'] ?? $this->generateTopicKey($data['title']);

        if ($heroImage) {
            $data['hero_image_url'] = $this->storeHeroUpload($heroImage, $topicKey);
        }

        return BlogPost::query()->create([
            'title' => $data['title'],
            'slug' => $slug,
            'topic_key' => $topicKey,
            'excerpt' => $data['excerpt'],
            'body' => $data['body'],
            'hero_image_url' => $data['hero_image_url'] ?? null,
            'meta_description' => $data['meta_description'] ?? Str::limit($data['excerpt'], 155),
            'source_url' => $data['source_url'] ?? null,
            'source_name' => $data['source_name'] ?? null,
            'tags' => $data['tags'] ?? [],
            'published_at' => $data['published_at'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(BlogPost $post, array $data, ?UploadedFile $heroImage = null): BlogPost
    {
        if ($post->title !== $data['title']) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $post->id);
        }

        if ($heroImage) {
            $this->deleteLocalHeroImage($post);
            $data['hero_image_url'] = $this->storeHeroUpload($heroImage, $post->topic_key);
        } elseif (! empty($data['remove_hero_image'])) {
            $this->deleteLocalHeroImage($post);
            $data['hero_image_url'] = null;
        }

        unset($data['remove_hero_image']);

        $post->update([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? $post->slug,
            'excerpt' => $data['excerpt'],
            'body' => $data['body'],
            'hero_image_url' => $data['hero_image_url'] ?? null,
            'meta_description' => $data['meta_description'] ?? Str::limit($data['excerpt'], 155),
            'source_url' => $data['source_url'] ?? null,
            'source_name' => $data['source_name'] ?? null,
            'tags' => $data['tags'] ?? [],
            'published_at' => $data['published_at'] ?? null,
        ]);

        return $post->fresh();
    }

    public function delete(BlogPost $post): void
    {
        $this->deleteLocalHeroImage($post);
        $post->delete();
    }

    public function generateTopicKey(string $title): string
    {
        $normalized = preg_replace('/[^\w\s]/u', ' ', mb_strtolower($title));
        $normalized = preg_replace('/\s+/', ' ', trim((string) $normalized));

        return hash('sha256', $normalized);
    }

    public function storeHeroUpload(UploadedFile $file, string $basename): string
    {
        $directory = public_path('images/blog');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
        $filename = $basename.'.'.$extension;

        $file->move($directory, $filename);

        return rtrim(config('app.url'), '/').'/images/blog/'.$filename;
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::limit($title, 80, ''));
        $slug = $base !== '' ? $base : 'post';
        $suffix = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId): bool
    {
        return BlogPost::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    private function deleteLocalHeroImage(BlogPost $post): void
    {
        $url = (string) $post->hero_image_url;

        if ($url === '' || ! str_contains($url, '/images/blog/')) {
            return;
        }

        $filename = basename(parse_url($url, PHP_URL_PATH) ?? '');
        $path = public_path('images/blog/'.$filename);

        if (is_file($path)) {
            unlink($path);
        }
    }
}
