<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internal\StoreInternalBlogPostRequest;
use App\Http\Requests\Internal\UpdateBlogHeroImageRequest;
use App\Models\BlogPost;
use App\Models\Job;
use App\Models\SalarySnapshot;
use App\Services\BlogIngestionService;
use Illuminate\Http\JsonResponse;

class InternalBlogController extends Controller
{
    public function __construct(
        private readonly BlogIngestionService $ingestion
    ) {}

    public function topicKeys(): JsonResponse
    {
        return response()->json([
            'topic_keys' => $this->ingestion->existingTopicKeys(),
        ]);
    }

    public function context(): JsonResponse
    {
        $snapshots = SalarySnapshot::query()
            ->orderByDesc('recorded_at')
            ->get()
            ->unique(fn ($row) => $row->stack.'|'.$row->country)
            ->sortBy(function ($row) {
                $country = strtolower((string) $row->country);
                $countryRank = match (true) {
                    str_contains($country, 'pakistan') => 0,
                    in_array($country, ['global', 'worldwide'], true) => 1,
                    in_array($country, ['remote'], true) => 2,
                    default => 3,
                };

                return [$countryRank, strtolower((string) $row->stack) === 'laravel' ? 0 : 1];
            })
            ->take(6)
            ->map(fn ($row) => [
                'stack' => $row->stack,
                'country' => $row->country,
                'avg_salary' => (int) $row->avg_salary,
            ])
            ->values();

        return response()->json([
            'active_jobs' => Job::query()->where('is_active', true)->count(),
            'salary_snapshots' => $snapshots,
        ]);
    }

    public function index(): JsonResponse
    {
        $posts = BlogPost::query()
            ->orderByDesc('published_at')
            ->get(['id', 'title', 'slug', 'topic_key', 'excerpt', 'body', 'hero_image_url', 'tags']);

        return response()->json([
            'posts' => $posts,
        ]);
    }

    public function updateHeroImage(UpdateBlogHeroImageRequest $request, BlogPost $blogPost): JsonResponse
    {
        $blogPost->update([
            'hero_image_url' => $request->validated('hero_image_url'),
        ]);

        return response()->json([
            'message' => 'Hero image updated.',
            'post' => $blogPost->fresh(),
        ]);
    }

    public function store(StoreInternalBlogPostRequest $request): JsonResponse
    {
        $result = $this->ingestion->storePost($request->validated());

        if ($result['status'] === 'duplicate') {
            return response()->json([
                'message' => 'A post with this topic_key already exists.',
            ], 409);
        }

        return response()->json([
            'message' => 'Blog post created.',
            'post' => $result['post'],
        ], 201);
    }
}
