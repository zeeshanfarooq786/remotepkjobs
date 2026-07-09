<?php

use App\Http\Controllers\Api\InternalAlternativeController;
use App\Http\Controllers\Api\InternalBlogController;
use App\Http\Controllers\Api\InternalJobController;
use App\Http\Controllers\Api\InternalRateController;
use Illuminate\Support\Facades\Route;

Route::prefix('internal')
    ->middleware(['internal.api', 'throttle:api'])
    ->group(function () {
        Route::get('/health', function () {
            return response()->json([
                'status' => 'ok',
                'service' => 'DevRates Internal API',
            ]);
        })->name('api.internal.health');

        Route::post('/exchange-rates', [InternalRateController::class, 'exchangeRates'])
            ->name('api.internal.exchange-rates.store');

        Route::post('/platform-rates', [InternalRateController::class, 'platformRates'])
            ->name('api.internal.platform-rates.store');

        Route::post('/jobs', [InternalJobController::class, 'store'])
            ->name('api.internal.jobs.store');

        Route::post('/salary-snapshot', [InternalJobController::class, 'salarySnapshot'])
            ->name('api.internal.salary-snapshot.store');

        Route::post('/alternatives/update', [InternalAlternativeController::class, 'update'])
            ->name('api.internal.alternatives.update');

        Route::get('/blog-posts/topic-keys', [InternalBlogController::class, 'topicKeys'])
            ->name('api.internal.blog-posts.topic-keys');

        Route::get('/blog-posts/context', [InternalBlogController::class, 'context'])
            ->name('api.internal.blog-posts.context');

        Route::get('/blog-posts', [InternalBlogController::class, 'index'])
            ->name('api.internal.blog-posts.index');

        Route::post('/blog-posts', [InternalBlogController::class, 'store'])
            ->name('api.internal.blog-posts.store');

        Route::patch('/blog-posts/{blogPost:id}/hero-image', [InternalBlogController::class, 'updateHeroImage'])
            ->name('api.internal.blog-posts.hero-image');
    });
