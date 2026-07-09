<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogPostRequest;
use App\Http\Requests\Admin\UpdateBlogPostRequest;
use App\Models\BlogPost;
use App\Services\AdminBlogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function __construct(
        private readonly AdminBlogService $adminBlog
    ) {}

    public function index(): View
    {
        $posts = BlogPost::query()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.blog.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.blog.create');
    }

    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        $post = $this->adminBlog->create(
            $this->normalizeInput($request->validated()),
            $request->file('hero_image')
        );

        return redirect()
            ->route('admin.blog.edit', $post)
            ->with('status', 'Blog post created.');
    }

    public function edit(BlogPost $blogPost): View
    {
        return view('admin.blog.edit', ['post' => $blogPost]);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $this->adminBlog->update(
            $blogPost,
            $this->normalizeInput($request->validated()),
            $request->file('hero_image')
        );

        return redirect()
            ->route('admin.blog.edit', $blogPost)
            ->with('status', 'Blog post updated.');
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $this->adminBlog->delete($blogPost);

        return redirect()
            ->route('admin.blog.index')
            ->with('status', 'Blog post deleted.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeInput(array $data): array
    {
        $tags = collect(explode(',', (string) ($data['tags'] ?? '')))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        $data['tags'] = $tags;

        if (empty($data['published_at'])) {
            $data['published_at'] = null;
        }

        $data['remove_hero_image'] = ! empty($data['remove_hero_image']);

        return $data;
    }
}
