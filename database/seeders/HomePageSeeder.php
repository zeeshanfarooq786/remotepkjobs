<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    public function run(): void
    {
        Page::updateOrCreate(
            ['slug' => 'home-footer'],
            [
                'title' => 'Home Footer SEO',
                'meta_description' => 'DevRates helps remote developers find jobs, calculate real take-home pay, and discover self-hosted tool alternatives.',
                'tool_type' => 'home',
                'published_at' => now(),
                'content_json' => [
                    'paragraph' => 'DevRates is built for freelance developers who need honest numbers, not marketing fluff. We aggregate remote developer jobs from trusted boards, normalize salary ranges, and surface roles for Laravel, Python, React, and Node stacks worldwide. Our freelancer fee calculator shows what you actually keep after Upwork, Fiverr, and Toptal platform fees, plus Payoneer, Wise, or local bank withdrawal costs, with live USD to PKR and other currency conversion. We also maintain a curated directory of self-hosted alternatives to expensive SaaS tools — Pusher, Mailgun, Heroku, and more — with GitHub stars, Docker support, and estimated monthly savings. Exchange rates and platform fees sync daily from public APIs and our internal rate updater. Whether you are in Pakistan, India, Nigeria, or working remotely from anywhere, DevRates helps you compare offers, plan withdrawals, and cut infrastructure costs without guesswork.',
                ],
            ]
        );
    }
}
