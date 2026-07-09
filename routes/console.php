<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('agent:run job_scraper')
    ->dailyAt('02:00')
    ->timezone('UTC');

Schedule::command('agent:run rate_updater')
    ->weeklyOn(0, '03:00')
    ->timezone('UTC');

Schedule::command('agent:run github_updater')
    ->weeklyOn(0, '04:00')
    ->timezone('UTC');

Schedule::command('jobs:clean-expired')
    ->dailyAt('06:00')
    ->timezone('UTC');

Schedule::command('exchange-rates:fetch')->daily();

Schedule::command('rates:check-freshness')
    ->dailyAt('07:00')
    ->timezone('UTC');

Schedule::command('sitemap:generate')
    ->dailyAt('08:00')
    ->timezone('UTC');

Schedule::command('agent:run blog_generator')
    ->dailyAt('09:00')
    ->timezone('UTC');
