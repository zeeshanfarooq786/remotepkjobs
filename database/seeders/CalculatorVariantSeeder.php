<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class CalculatorVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishedAt = now();

        $variants = [
            [
                'slug' => 'upwork-pakistan-pkr',
                'title' => 'Upwork Pakistan PKR Calculator — DevRates',
                'meta_description' => 'Upwork Pakistan PKR calculator: see take-home pay after 10% Upwork fee and Payoneer withdrawal. Convert USD earnings to PKR with live rates for freelancers.',
                'h1' => 'Upwork Pakistan PKR Calculator',
                'intro' => 'Calculate how much of your Upwork earnings reach your Pakistani bank account after platform and Payoneer fees.',
                'defaults' => ['amount' => 1000, 'platform' => 'upwork', 'processor' => 'payoneer', 'currency' => 'USD'],
            ],
            [
                'slug' => 'fiverr-payoneer-pakistan',
                'title' => 'Fiverr Payoneer Pakistan Calculator — DevRates',
                'meta_description' => 'Fiverr Payoneer Pakistan calculator for sellers. Estimate net PKR after 20% Fiverr fee and Payoneer withdrawal costs with live USD to PKR conversion.',
                'h1' => 'Fiverr Payoneer Pakistan Calculator',
                'intro' => 'See what Fiverr sellers in Pakistan actually keep after Fiverr and Payoneer take their cut.',
                'defaults' => ['amount' => 500, 'platform' => 'fiverr', 'processor' => 'payoneer', 'currency' => 'USD'],
            ],
            [
                'slug' => 'upwork-wise-withdrawal',
                'title' => 'Upwork Wise Withdrawal Calculator — DevRates',
                'meta_description' => 'Upwork Wise withdrawal calculator: compare take-home pay after Upwork fees and Wise transfer costs. Instant USD conversion for international freelancers.',
                'h1' => 'Upwork Wise Withdrawal Calculator',
                'intro' => 'Compare Upwork earnings after Wise transfer fees versus Payoneer or direct bank withdrawal.',
                'defaults' => ['amount' => 2000, 'platform' => 'upwork', 'processor' => 'wise', 'currency' => 'USD'],
            ],
            [
                'slug' => 'freelancer-tax-calculator-pakistan',
                'title' => 'Freelancer Tax Calculator Pakistan — DevRates',
                'meta_description' => 'Freelancer tax calculator Pakistan: estimate net income after Upwork fees, bank withdrawal, and currency conversion. Plan take-home pay before tax season.',
                'h1' => 'Freelancer Tax Calculator Pakistan',
                'intro' => 'Estimate net freelance income after platform fees and local bank costs — a starting point for tax planning.',
                'defaults' => ['amount' => 1500, 'platform' => 'upwork', 'processor' => 'direct', 'currency' => 'USD'],
            ],
            [
                'slug' => 'toptal-withdrawal-pkr',
                'title' => 'Toptal Withdrawal PKR Calculator — DevRates',
                'meta_description' => 'Toptal withdrawal PKR calculator for developers. See net PKR after Toptal payouts, Wise transfer fees, and live exchange rates for high-earning freelancers.',
                'h1' => 'Toptal Withdrawal PKR Calculator',
                'intro' => 'Toptal bills clients, not freelancers — see what you keep after Wise fees when withdrawing to Pakistan.',
                'defaults' => ['amount' => 5000, 'platform' => 'toptal', 'processor' => 'wise', 'currency' => 'USD'],
            ],
            [
                'slug' => 'fiverr-wise-usd-to-pkr',
                'title' => 'Fiverr Wise USD to PKR Calculator — DevRates',
                'meta_description' => 'Fiverr Wise USD to PKR calculator: net take-home after 20% Fiverr fee and Wise transfer costs. Live rates for Pakistani Fiverr sellers withdrawing earnings.',
                'h1' => 'Fiverr Wise USD to PKR Calculator',
                'intro' => 'Convert Fiverr USD earnings to PKR after Fiverr and Wise fees — updated with daily exchange rates.',
                'defaults' => ['amount' => 800, 'platform' => 'fiverr', 'processor' => 'wise', 'currency' => 'USD'],
            ],
            [
                'slug' => 'upwork-dollar-to-pkr-2026',
                'title' => 'Upwork Dollar to PKR 2026 Calculator — DevRates',
                'meta_description' => 'Upwork dollar to PKR 2026 calculator with live rates. Calculate take-home PKR after Upwork fees and Payoneer withdrawal for Pakistani freelancers this year.',
                'h1' => 'Upwork Dollar to PKR 2026',
                'intro' => 'Updated for 2026 with live USD/PKR rates — see what your Upwork dollars are worth after all fees.',
                'defaults' => ['amount' => 3000, 'platform' => 'upwork', 'processor' => 'payoneer', 'currency' => 'USD'],
            ],
            [
                'slug' => 'payoneer-fee-calculator-freelancer',
                'title' => 'Payoneer Fee Calculator Freelancer — DevRates',
                'meta_description' => 'Payoneer fee calculator for freelancers on Fiverr and Upwork. Estimate withdrawal costs and net take-home pay in PKR with live platform and conversion fees.',
                'h1' => 'Payoneer Fee Calculator for Freelancers',
                'intro' => 'Isolate Payoneer withdrawal costs on top of Fiverr or Upwork platform fees.',
                'defaults' => ['amount' => 1200, 'platform' => 'fiverr', 'processor' => 'payoneer', 'currency' => 'USD'],
            ],
            [
                'slug' => 'wise-transfer-fee-freelancer-pakistan',
                'title' => 'Wise Transfer Fee Freelancer Pakistan — DevRates',
                'meta_description' => 'Wise transfer fee freelancer Pakistan calculator. Compare Upwork take-home pay after Wise fees versus Payoneer with live USD to PKR conversion rates.',
                'h1' => 'Wise Transfer Fee Calculator Pakistan',
                'intro' => 'See whether Wise saves you money over Payoneer on Upwork withdrawals to Pakistan.',
                'defaults' => ['amount' => 2500, 'platform' => 'upwork', 'processor' => 'wise', 'currency' => 'USD'],
            ],
            [
                'slug' => 'fiverr-seller-withdrawal-pakistan',
                'title' => 'Fiverr Seller Withdrawal Pakistan — DevRates',
                'meta_description' => 'Fiverr seller withdrawal Pakistan calculator: net PKR after 20% Fiverr fee and local bank costs. Plan take-home pay before accepting new gigs.',
                'h1' => 'Fiverr Seller Withdrawal Pakistan',
                'intro' => 'Calculate Fiverr seller take-home pay when withdrawing directly to a Pakistani bank account.',
                'defaults' => ['amount' => 600, 'platform' => 'fiverr', 'processor' => 'direct', 'currency' => 'USD'],
            ],
        ];

        foreach ($variants as $variant) {
            Page::updateOrCreate(
                ['slug' => $variant['slug']],
                [
                    'title' => $variant['title'],
                    'meta_description' => $variant['meta_description'],
                    'tool_type' => 'calculator',
                    'published_at' => $publishedAt,
                    'content_json' => [
                        'h1' => $variant['h1'],
                        'intro' => $variant['intro'],
                        'defaults' => $variant['defaults'],
                    ],
                ]
            );
        }
    }
}
