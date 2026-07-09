<?php

namespace App\Http\Controllers;

use App\Services\SeoService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        private readonly SeoService $seo
    ) {}

    public function about(): View
    {
        return view('pages.about', [
            'seo' => $this->seo->getAboutMeta(),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'seo' => $this->seo->getContactMeta(),
        ]);
    }

    public function privacy(): View
    {
        return view('pages.privacy', [
            'seo' => $this->seo->getPrivacyMeta(),
        ]);
    }
}
