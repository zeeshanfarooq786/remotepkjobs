<?php

namespace App\Services;

use App\Models\Alternative;
use App\Models\BlogPost;
use App\Models\Job;
use App\Models\Page;
use App\Models\SalarySnapshot;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    public function generate(): Sitemap
    {
        $sitemap = Sitemap::create();

        $sitemap->add(
            Url::create(route('home'))
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );

        $sitemap->add(
            Url::create(route('calculator.index'))
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );

        Page::query()
            ->where('tool_type', 'calculator')
            ->where('slug', '!=', 'calculator-footer')
            ->whereNotNull('published_at')
            ->orderBy('slug')
            ->get()
            ->each(function (Page $page) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('calculator.variant', $page->slug))
                        ->setLastModificationDate($page->updated_at)
                        ->setPriority(0.9)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            });

        $sitemap->add(
            Url::create(route('tools.index'))
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );

        $sitemap->add(
            Url::create(route('blog.index'))
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );

        BlogPost::query()
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->get()
            ->each(function (BlogPost $post) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('blog.show', $post->slug))
                        ->setLastModificationDate($post->updated_at ?? $post->published_at ?? now())
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            });

        Alternative::query()
            ->whereNotNull('slug')
            ->orderBy('slug')
            ->get()
            ->each(function (Alternative $alternative) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('tools.show', $alternative->slug))
                        ->setLastModificationDate($alternative->updated_at)
                        ->setPriority(0.8)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            });

        foreach (['about', 'contact', 'privacy'] as $staticRoute) {
            $sitemap->add(
                Url::create(route($staticRoute))
                    ->setPriority(0.5)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            );
        }

        $sitemap->add(
            Url::create(route('jobs.index'))
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );

        Job::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->orderByDesc('posted_at')
            ->get()
            ->each(function (Job $job) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('jobs.show', $job->slug))
                        ->setLastModificationDate($job->updated_at ?? $job->posted_at ?? now())
                        ->setPriority(0.8)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                );
            });

        SalarySnapshot::query()
            ->select('stack', 'country')
            ->distinct()
            ->orderBy('stack')
            ->orderBy('country')
            ->get()
            ->each(function (SalarySnapshot $snapshot) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('jobs.salary', [
                        strtolower($snapshot->stack),
                        strtolower($snapshot->country),
                    ]))
                        ->setPriority(0.8)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                );
            });

        return $sitemap;
    }
}
