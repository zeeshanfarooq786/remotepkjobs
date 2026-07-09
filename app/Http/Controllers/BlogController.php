<?php

namespace App\Http\Controllers;

use App\Services\BlogService;
use App\Services\SeoService;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(
        private readonly BlogService $blog,
        private readonly SeoService $seo
    ) {}

    public function index(): View
    {
        return view('blog.index', [
            'posts' => $this->blog->getPublishedPosts(),
            'seo' => $this->seo->getBlogIndexMeta(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = $this->blog->findPublishedBySlug($slug);

        abort_if($post === null, 404);

        return view('blog.show', [
            'post' => $post,
            'seo' => $this->seo->getBlogPostMeta($post),
        ]);
    }
}
