<?php

namespace Database\Seeders;

use App\Models\Alternative;
use App\Models\ExchangeRate;
use App\Models\Job;
use App\Models\Page;
use App\Models\PlatformRate;
use App\Models\SalarySnapshot;
use App\Services\AlternativeService;
use App\Services\JobService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedPlatformRates();
        $this->seedExchangeRates();
        $this->seedAlternatives();
        $this->seedJobs();
        $this->seedSalarySnapshots();
        $this->seedCalculatorPages();
        $this->call(CalculatorVariantSeeder::class);
        $this->call(HomePageSeeder::class);
    }

    private function seedPlatformRates(): void
    {
        $effectiveDate = now()->toDateString();

        $rates = [
            ['platform' => 'Upwork', 'fee_type' => 'flat_percent', 'fee_value' => 10, 'currency' => 'USD'],
            ['platform' => 'Fiverr', 'fee_type' => 'flat_percent', 'fee_value' => 20, 'currency' => 'USD'],
            ['platform' => 'Toptal', 'fee_type' => 'flat_percent', 'fee_value' => 0, 'currency' => 'USD'],
            ['platform' => 'Payoneer', 'fee_type' => 'withdrawal_percent', 'fee_value' => 2, 'currency' => 'USD'],
            ['platform' => 'Wise', 'fee_type' => 'transfer_percent', 'fee_value' => 0.5, 'currency' => 'USD'],
            ['platform' => 'HBL_PKR', 'fee_type' => 'bank_withdrawal_flat', 'fee_value' => 1, 'currency' => 'USD'],
        ];

        foreach ($rates as $rate) {
            PlatformRate::updateOrCreate(
                [
                    'platform' => $rate['platform'],
                    'fee_type' => $rate['fee_type'],
                ],
                [
                    'fee_value' => $rate['fee_value'],
                    'currency' => $rate['currency'],
                    'effective_date' => $effectiveDate,
                ]
            );
        }
    }

    private function seedExchangeRates(): void
    {
        $recordedAt = now();

        $rates = [
            ['from_currency' => 'USD', 'to_currency' => 'PKR', 'rate' => 278.000000],
            ['from_currency' => 'USD', 'to_currency' => 'EUR', 'rate' => 0.920000],
            ['from_currency' => 'USD', 'to_currency' => 'GBP', 'rate' => 0.790000],
            ['from_currency' => 'USD', 'to_currency' => 'CAD', 'rate' => 1.370000],
            ['from_currency' => 'USD', 'to_currency' => 'AUD', 'rate' => 1.540000],
            ['from_currency' => 'USD', 'to_currency' => 'AED', 'rate' => 3.670000],
            ['from_currency' => 'USD', 'to_currency' => 'SAR', 'rate' => 3.750000],
            ['from_currency' => 'USD', 'to_currency' => 'BDT', 'rate' => 110.000000],
            ['from_currency' => 'USD', 'to_currency' => 'INR', 'rate' => 83.000000],
            ['from_currency' => 'USD', 'to_currency' => 'NGN', 'rate' => 1580.000000],
        ];

        foreach ($rates as $rate) {
            ExchangeRate::updateOrCreate(
                [
                    'from_currency' => $rate['from_currency'],
                    'to_currency' => $rate['to_currency'],
                ],
                [
                    'rate' => $rate['rate'],
                    'recorded_at' => $recordedAt,
                ]
            );
        }
    }

    private function seedAlternatives(): void
    {
        $service = app(AlternativeService::class);

        $alternatives = [
            [
                'paid_tool' => 'Pusher',
                'open_tool' => 'Soketi',
                'category' => 'websockets',
                'github_stars' => 4200,
                'github_forks' => 310,
                'last_commit' => now()->subDays(3),
                'monthly_cost_paid' => 49.00,
                'docker_support' => true,
                'php_version_req' => '8.1',
                'laravel_compatible' => true,
                'description' => 'Self-hosted WebSocket server compatible with the Pusher protocol for Laravel broadcasting.',
                'comparison_json' => [
                    'install_snippet' => "docker run -p 6001:6001 quay.io/soketi/soketi:latest-16-alpine\nBROADCAST_DRIVER=pusher\nPUSHER_HOST=127.0.0.1\nPUSHER_PORT=6001",
                    'features' => [
                        ['feature' => 'Laravel broadcasting', 'paid' => 'Native SDK', 'open' => 'Pusher-compatible'],
                        ['feature' => 'Private channels', 'paid' => 'Yes', 'open' => 'Yes'],
                        ['feature' => 'Docker deploy', 'paid' => 'N/A (SaaS)', 'open' => 'Yes'],
                        ['feature' => 'Monthly cost', 'paid' => '$49+', 'open' => 'VPS only'],
                    ],
                ],
            ],
            [
                'paid_tool' => 'Mailgun',
                'open_tool' => 'Postal',
                'category' => 'email',
                'github_stars' => 13800,
                'github_forks' => 980,
                'last_commit' => now()->subDays(7),
                'monthly_cost_paid' => 35.00,
                'docker_support' => true,
                'php_version_req' => '8.2',
                'laravel_compatible' => true,
                'description' => 'Open-source mail delivery platform for high-volume transactional email.',
                'comparison_json' => [
                    'install_snippet' => "MAIL_MAILER=smtp\nMAIL_HOST=postal.yourdomain.com\nMAIL_PORT=587\nMAIL_USERNAME=api-key",
                    'features' => [
                        ['feature' => 'Transactional email', 'paid' => 'Yes', 'open' => 'Yes'],
                        ['feature' => 'Webhooks', 'paid' => 'Yes', 'open' => 'Yes'],
                        ['feature' => 'Self-hosted', 'paid' => 'No', 'open' => 'Yes'],
                        ['feature' => 'Monthly cost', 'paid' => '$35+', 'open' => 'VPS only'],
                    ],
                ],
            ],
            [
                'paid_tool' => 'PlanetScale',
                'open_tool' => 'MySQL 8',
                'category' => 'storage',
                'github_stars' => 0,
                'github_forks' => 0,
                'last_commit' => null,
                'monthly_cost_paid' => 29.00,
                'docker_support' => true,
                'php_version_req' => '8.1',
                'laravel_compatible' => true,
                'description' => 'Run MySQL 8 on your own VPS or locally with Laravel migrations and backups.',
                'comparison_json' => [
                    'install_snippet' => "DB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=laravel",
                    'features' => [
                        ['feature' => 'Branching', 'paid' => 'Yes', 'open' => 'Manual backups'],
                        ['feature' => 'Serverless scaling', 'paid' => 'Yes', 'open' => 'Manual VPS scale'],
                        ['feature' => 'Laravel support', 'paid' => 'Yes', 'open' => 'Yes'],
                        ['feature' => 'Monthly cost', 'paid' => '$29+', 'open' => 'VPS only'],
                    ],
                ],
            ],
            [
                'paid_tool' => 'Heroku',
                'open_tool' => 'Coolify',
                'category' => 'hosting',
                'github_stars' => 35000,
                'github_forks' => 2100,
                'last_commit' => now()->subDay(),
                'monthly_cost_paid' => 25.00,
                'docker_support' => true,
                'php_version_req' => '8.2',
                'laravel_compatible' => true,
                'description' => 'Self-hosted PaaS for deploying Laravel apps with Docker on your own VPS.',
                'comparison_json' => [
                    'install_snippet' => "curl -fsSL https://cdn.coollabs.io/coolify/install.sh | bash",
                    'features' => [
                        ['feature' => 'Git deploy', 'paid' => 'Yes', 'open' => 'Yes'],
                        ['feature' => 'SSL automation', 'paid' => 'Yes', 'open' => 'Yes'],
                        ['feature' => 'Multi-app hosting', 'paid' => 'Paid dynos', 'open' => 'Unlimited on VPS'],
                        ['feature' => 'Monthly cost', 'paid' => '$25+', 'open' => 'VPS only'],
                    ],
                ],
            ],
            [
                'paid_tool' => 'Datadog',
                'open_tool' => 'SigNoz',
                'category' => 'analytics',
                'github_stars' => 18000,
                'github_forks' => 1200,
                'last_commit' => now()->subDays(2),
                'monthly_cost_paid' => 20.00,
                'docker_support' => true,
                'php_version_req' => '8.1',
                'laravel_compatible' => true,
                'description' => 'Open-source APM and observability platform with Laravel OpenTelemetry support.',
                'comparison_json' => [
                    'install_snippet' => "git clone https://github.com/SigNoz/signoz.git\ncd signoz/deploy\ndocker compose up -d",
                    'features' => [
                        ['feature' => 'APM traces', 'paid' => 'Yes', 'open' => 'Yes'],
                        ['feature' => 'Dashboards', 'paid' => 'Yes', 'open' => 'Yes'],
                        ['feature' => 'OpenTelemetry', 'paid' => 'Yes', 'open' => 'Yes'],
                        ['feature' => 'Monthly cost', 'paid' => '$20+', 'open' => 'VPS only'],
                    ],
                ],
            ],
            [
                'paid_tool' => 'Amazon SQS',
                'open_tool' => 'Redis + Horizon',
                'category' => 'queue',
                'github_stars' => 3800,
                'github_forks' => 620,
                'last_commit' => now()->subDays(5),
                'monthly_cost_paid' => 15.00,
                'docker_support' => true,
                'php_version_req' => '8.2',
                'laravel_compatible' => true,
                'description' => 'Replace managed SQS with Redis and Laravel Horizon for queue monitoring on your own server.',
                'comparison_json' => [
                    'install_snippet' => "composer require laravel/horizon\nphp artisan horizon:install\nQUEUE_CONNECTION=redis",
                    'features' => [
                        ['feature' => 'Queue workers', 'paid' => 'Managed', 'open' => 'Self-hosted'],
                        ['feature' => 'Dashboard', 'paid' => 'CloudWatch', 'open' => 'Horizon UI'],
                        ['feature' => 'Laravel native', 'paid' => 'Via driver', 'open' => 'First-class'],
                        ['feature' => 'Monthly cost', 'paid' => '$15+', 'open' => 'VPS only'],
                    ],
                ],
            ],
        ];

        foreach ($alternatives as $alternative) {
            $alternative['slug'] = $service->generateSlug($alternative['paid_tool']);

            Alternative::updateOrCreate(
                ['paid_tool' => $alternative['paid_tool']],
                $alternative
            );
        }
    }

    private function seedJobs(): void
    {
        $jobs = [
            [
                'title' => 'Senior Laravel Developer',
                'company' => 'Toptal',
                'salary_min' => 5000,
                'salary_max' => 8000,
                'currency' => 'USD',
                'stack' => 'Laravel',
                'country' => 'Pakistan',
                'remote_type' => 'fully_remote',
                'source_url' => 'https://www.toptal.com/careers/senior-laravel-developer-remote',
                'posted_at' => now()->subDays(2),
                'is_active' => true,
            ],
            [
                'title' => 'Laravel Backend Engineer',
                'company' => 'Automattic',
                'salary_min' => 4500,
                'salary_max' => 7500,
                'currency' => 'USD',
                'stack' => 'Laravel',
                'country' => 'Global',
                'remote_type' => 'fully_remote',
                'source_url' => 'https://automattic.com/work-with-us/laravel-backend-engineer',
                'posted_at' => now()->subDays(4),
                'is_active' => true,
            ],
            [
                'title' => 'Full Stack Laravel Developer',
                'company' => 'Laravel LLC',
                'salary_min' => 6000,
                'salary_max' => 9000,
                'currency' => 'USD',
                'stack' => 'Laravel',
                'country' => 'Global',
                'remote_type' => 'fully_remote',
                'source_url' => 'https://laravel.com/careers/full-stack-developer',
                'posted_at' => now()->subDays(1),
                'is_active' => true,
            ],
            [
                'title' => 'Python Django Developer',
                'company' => 'Canonical',
                'salary_min' => 4000,
                'salary_max' => 7000,
                'currency' => 'USD',
                'stack' => 'Python',
                'country' => 'Pakistan',
                'remote_type' => 'fully_remote',
                'source_url' => 'https://canonical.com/careers/python-django-developer-remote',
                'posted_at' => now()->subDays(3),
                'is_active' => true,
            ],
            [
                'title' => 'Senior Python Engineer',
                'company' => 'GitLab',
                'salary_min' => 5500,
                'salary_max' => 8500,
                'currency' => 'USD',
                'stack' => 'Python',
                'country' => 'Global',
                'remote_type' => 'fully_remote',
                'source_url' => 'https://about.gitlab.com/jobs/senior-python-engineer',
                'posted_at' => now()->subDays(5),
                'is_active' => true,
            ],
            [
                'title' => 'Laravel + Vue Developer',
                'company' => 'Buffer',
                'salary_min' => 3500,
                'salary_max' => 5500,
                'currency' => 'USD',
                'stack' => 'Laravel',
                'country' => 'Global',
                'remote_type' => 'fully_remote',
                'source_url' => 'https://buffer.com/jobs/laravel-vue-developer',
                'posted_at' => now()->subDays(6),
                'is_active' => true,
            ],
            [
                'title' => 'Python FastAPI Backend Developer',
                'company' => 'Stripe',
                'salary_min' => 7000,
                'salary_max' => 11000,
                'currency' => 'USD',
                'stack' => 'Python',
                'country' => 'Global',
                'remote_type' => 'fully_remote',
                'source_url' => 'https://stripe.com/jobs/python-fastapi-backend-developer',
                'posted_at' => now()->subDays(2),
                'is_active' => true,
            ],
            [
                'title' => 'Mid-Level Laravel Developer',
                'company' => 'RemoteOK',
                'salary_min' => 3000,
                'salary_max' => 5000,
                'currency' => 'USD',
                'stack' => 'Laravel',
                'country' => 'Pakistan',
                'remote_type' => 'fully_remote',
                'source_url' => 'https://remoteok.com/remote-jobs/mid-level-laravel-developer-pakistan',
                'posted_at' => now()->subDays(7),
                'is_active' => true,
            ],
            [
                'title' => 'Python Data Engineer',
                'company' => 'Zapier',
                'salary_min' => 5000,
                'salary_max' => 8000,
                'currency' => 'USD',
                'stack' => 'Python',
                'country' => 'Global',
                'remote_type' => 'fully_remote',
                'source_url' => 'https://zapier.com/jobs/python-data-engineer-remote',
                'posted_at' => now()->subDays(8),
                'is_active' => true,
            ],
            [
                'title' => 'Laravel API Developer',
                'company' => 'Basecamp',
                'salary_min' => 4000,
                'salary_max' => 6500,
                'currency' => 'USD',
                'stack' => 'Laravel',
                'country' => 'Pakistan',
                'remote_type' => 'fully_remote',
                'source_url' => 'https://basecamp.com/about/jobs/laravel-api-developer',
                'posted_at' => now()->subDays(9),
                'is_active' => true,
            ],
        ];

        foreach ($jobs as $job) {
            $record = Job::updateOrCreate(
                ['source_url' => $job['source_url']],
                $job
            );

            if (! $record->slug) {
                $record->slug = app(JobService::class)->generateSlug($job);
                $record->save();
            }
        }
    }

    private function seedSalarySnapshots(): void
    {
        $series = [
            ['stack' => 'Laravel', 'country' => 'Pakistan', 'base' => 4000],
            ['stack' => 'Laravel', 'country' => 'Global', 'base' => 5600],
            ['stack' => 'Python', 'country' => 'Pakistan', 'base' => 4300],
            ['stack' => 'Python', 'country' => 'Global', 'base' => 6400],
            ['stack' => 'Python', 'country' => 'India', 'base' => 3800],
            ['stack' => 'Laravel', 'country' => 'India', 'base' => 3600],
        ];

        foreach ($series as $item) {
            for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
                $recordedAt = now()->subMonths($monthsAgo)->startOfMonth();
                $avgSalary = (int) ($item['base'] + ($monthsAgo * 50) + rand(-100, 100));

                SalarySnapshot::updateOrCreate(
                    [
                        'stack' => $item['stack'],
                        'country' => $item['country'],
                        'recorded_at' => $recordedAt,
                    ],
                    [
                        'avg_salary' => $avgSalary,
                        'sample_size' => rand(3, 12),
                    ]
                );
            }
        }
    }

    private function seedCalculatorPages(): void
    {
        $publishedAt = now();

        Page::updateOrCreate(
            ['slug' => 'calculator-footer'],
            [
                'title' => 'Calculator Footer SEO',
                'meta_description' => 'How the DevRates freelancer fee calculator computes take-home pay for developers worldwide.',
                'tool_type' => 'calculator',
                'published_at' => $publishedAt,
                'content_json' => [
                    'paragraphs' => [
                        'This calculator uses current platform fees: Upwork charges a flat 10%, Fiverr charges 20%, and Toptal charges freelancers 0% because fees are billed to the client.',
                        'After platform fees, we deduct payout processor costs — Payoneer (~2%), Wise (~0.5%), or a flat local bank fee — then convert the remaining USD balance using live exchange rates synced daily from open.er-api.com.',
                        'All calculations run instantly in your browser. Rates are loaded once on page load so your results update immediately as you type, with no server round-trips.',
                    ],
                ],
            ]
        );

    }
}
