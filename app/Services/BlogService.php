<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BlogService
{
    public function getPublishedPosts(int $perPage = 12): LengthAwarePaginator
    {
        return BlogPost::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    public function getLatestPosts(int $limit = 3)
    {
        return BlogPost::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function findPublishedBySlug(string $slug): ?BlogPost
    {
        return BlogPost::query()
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->first();
    }
}
