<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Write public/sitemap.xml from database routes';

    public function handle(SitemapService $sitemap): int
    {
        $path = public_path('sitemap.xml');

        $sitemap->generate()->writeToFile($path);

        $this->info("Sitemap written to {$path}");

        return self::SUCCESS;
    }
}
