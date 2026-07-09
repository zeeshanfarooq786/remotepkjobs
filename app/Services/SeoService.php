<?php

namespace App\Services;

use App\Models\Alternative;
use App\Models\Job;
use App\Models\Page;
use Illuminate\Support\Str;

class SeoService
{
    private const SUFFIX = ' — DevRates';

    private const DEFAULT_DESCRIPTION = 'DevRates helps freelance developers find remote jobs, calculate platform fees and withdrawal costs, and compare self-hosted tool alternatives.';

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getJobMeta(Job $job): array
    {
        $keyword = 'remote '.$job->stack.' job';
        $primary = $job->title.' at '.$job->company;

        $description = $this->clampDescription(
            "Remote {$job->stack} job at {$job->company}. {$keyword} with salary data, apply link, and take-home calculator for freelancers worldwide.",
            $keyword
        );

        return $this->buildMeta(
            $this->title($primary),
            $description,
            route('jobs.show', $job->slug),
            'article'
        );
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getCalculatorMeta(string $variant): array
    {
        if ($variant === '' || $variant === 'default') {
            $keyword = 'freelancer fee calculator';
            $primary = 'Freelancer Fee Calculator';
            $description = $this->clampDescription(
                'Free '.$keyword.' for Upwork, Fiverr, and Toptal. See take-home pay after platform fees, Payoneer, Wise, and live USD to PKR conversion.',
                $keyword
            );
            $url = route('calculator.index');

            return $this->buildMeta($this->title($primary), $description, $url, 'website');
        }

        $page = Page::query()
            ->where('slug', $variant)
            ->where('tool_type', 'calculator')
            ->first();

        $keyword = str_replace('-', ' ', $variant).' calculator';
        $primary = $page?->content['h1'] ?? Str::title(str_replace('-', ' ', $variant));
        $description = $this->clampDescription(
            $page?->meta_description ?? 'Calculate '.$keyword.' take-home pay after platform fees, withdrawal costs, and currency conversion for freelancers.',
            $keyword
        );
        $url = route('calculator.variant', $variant);

        return $this->buildMeta($this->title($primary), $description, $url, 'website');
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getToolMeta(Alternative $alternative): array
    {
        $keyword = $alternative->paid_tool.' alternative';
        $primary = $alternative->paid_tool.' → '.$alternative->open_tool;
        $savings = number_format((float) $alternative->monthly_cost_paid, 0);

        $description = $this->clampDescription(
            "Compare {$keyword} with {$alternative->open_tool}. Save \${$savings}/mo self-hosting. GitHub stats, install guide, and Laravel compatibility for developers.",
            $keyword
        );

        return $this->buildMeta(
            $this->title($primary),
            $description,
            route('tools.show', $alternative->slug),
            'article'
        );
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getSalaryMeta(string $stack, string $country): array
    {
        $stackLabel = ucfirst(strtolower($stack));
        $countryLabel = ucfirst(strtolower($country));
        $keyword = "{$stackLabel} developer salary {$countryLabel}";
        $primary = "{$stackLabel} Salary in {$countryLabel}";

        $description = $this->clampDescription(
            "{$keyword} data for remote freelancers. Average monthly pay, hiring companies, 6-month trend chart, and related {$stackLabel} jobs updated daily.",
            $keyword
        );

        return $this->buildMeta(
            $this->title($primary),
            $description,
            route('jobs.salary', [strtolower($stack), strtolower($country)]),
            'article'
        );
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getToolsIndexMeta(): array
    {
        $keyword = 'self-hosted dev tools';
        $primary = 'Self-Hosted Dev Tool Alternatives';

        $description = $this->clampDescription(
            'Browse '.$keyword.' for Laravel developers. Compare Pusher, Mailgun, Heroku alternatives with GitHub stars, Docker support, and monthly savings.',
            $keyword
        );

        return $this->buildMeta($this->title($primary), $description, route('tools.index'), 'website');
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getJobsIndexMeta(): array
    {
        $keyword = 'remote developer jobs';
        $primary = 'Remote Developer Jobs';

        $description = $this->clampDescription(
            'Find '.$keyword.' for Laravel, Python, React, and Node. Filter by salary, country, and remote type. Fresh listings with take-home pay calculators.',
            $keyword
        );

        return $this->buildMeta($this->title($primary), $description, route('jobs.index'), 'website');
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getHomeMeta(): array
    {
        $keyword = 'remote developer salary';
        $primary = 'What Remote Developers Earn';

        $description = $this->clampDescription(
            'Discover '.$keyword.' and real take-home pay after platform fees. Browse remote jobs, run the freelancer calculator, and compare self-hosted dev tools.',
            $keyword
        );

        return $this->buildMeta($this->title($primary), $description, route('home'), 'website');
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getAboutMeta(): array
    {
        $primary = 'About DevRates';

        $description = $this->clampDescription(
            'Learn how DevRates helps freelance developers find remote jobs, calculate platform and withdrawal fees, and replace expensive SaaS with self-hosted tools.',
            'about DevRates'
        );

        return $this->buildMeta($this->title($primary), $description, route('about'), 'website');
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getContactMeta(): array
    {
        $primary = 'Contact DevRates';

        $description = $this->clampDescription(
            'Contact DevRates for support, corrections, partnerships, or press inquiries about remote developer jobs and freelancer tools.',
            'contact DevRates'
        );

        return $this->buildMeta($this->title($primary), $description, route('contact'), 'website');
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getPrivacyMeta(): array
    {
        $primary = 'Privacy Policy';

        $description = $this->clampDescription(
            'DevRates privacy policy covering cookies, Google AdSense, analytics, and how we handle data from job listings and calculator usage.',
            'DevRates privacy policy'
        );

        return $this->buildMeta($this->title($primary), $description, route('privacy'), 'website');
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getBlogIndexMeta(): array
    {
        $primary = 'Tech Blog for Remote Developers';

        $description = $this->clampDescription(
            'Daily tech and remote work insights for freelance developers. Trends in salaries, tools, Upwork, and self-hosting — curated for Pakistan and worldwide devs.',
            'remote developer tech blog'
        );

        return $this->buildMeta($this->title($primary), $description, route('blog.index'), 'website');
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    public function getBlogPostMeta(\App\Models\BlogPost $post): array
    {
        $description = $this->clampDescription(
            $post->meta_description ?? $post->excerpt,
            'remote developer'
        );

        return $this->buildMeta(
            $this->title($post->title),
            $description,
            route('blog.show', $post->slug),
            'article'
        );
    }

    /**
     * @return array{title: string, description: string, og_title: string, og_description: string, og_url: string, og_type: string, canonical: string}
     */
    private function buildMeta(string $title, string $description, string $url, string $ogType): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'og_title' => $title,
            'og_description' => $description,
            'og_url' => $url,
            'og_type' => $ogType,
            'canonical' => $url,
        ];
    }

    private function title(string $primary): string
    {
        $maxPrimary = 60 - mb_strlen(self::SUFFIX);

        if (mb_strlen($primary) > $maxPrimary) {
            $primary = rtrim(mb_substr($primary, 0, max($maxPrimary - 3, 1))).'...';
        }

        return $primary.self::SUFFIX;
    }

    private function clampDescription(string $text, string $keyword): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? self::DEFAULT_DESCRIPTION);

        if (! str_contains(strtolower($text), strtolower($keyword))) {
            $text = rtrim($text, '.').'. '.$keyword.' insights on DevRates.';
        }

        if (mb_strlen($text) > 155) {
            $text = rtrim(mb_substr($text, 0, 152)).'...';
        }

        if (mb_strlen($text) < 140) {
            $padding = ' Updated daily on DevRates for freelance developers.';
            $text = rtrim(mb_substr($text.' '.$padding, 0, 155));
        }

        return $text;
    }
}
