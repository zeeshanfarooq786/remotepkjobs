<?php

namespace App\Http\Controllers;

use App\Services\HomeService;
use App\Services\SeoService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeService $home,
        private readonly SeoService $seo
    ) {}

    public function index(): View
    {
        return view('home', [
            ...$this->home->getHomeData(),
            'seo' => $this->seo->getHomeMeta(),
        ]);
    }
}
