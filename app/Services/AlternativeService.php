<?php

namespace App\Services;

use App\Models\Alternative;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AlternativeService
{
    public const CATEGORY_ORDER = [
        'websockets',
        'email',
        'analytics',
        'queue',
        'storage',
        'hosting',
    ];

    public const CATEGORY_LABELS = [
        'websockets' => 'Websockets',
        'email' => 'Email',
        'analytics' => 'Analytics',
        'queue' => 'Queue',
        'storage' => 'Storage',
        'hosting' => 'Hosting',
        'database' => 'Storage',
        'monitoring' => 'Analytics',
    ];

    public function getFilteredAlternatives(array $filters): Collection
    {
        $query = Alternative::query();

        if (! empty($filters['docker'])) {
            $query->where('docker_support', true);
        }

        if (! empty($filters['laravel'])) {
            $query->where('laravel_compatible', true);
        }

        if (! empty($filters['php_version'])) {
            $query->where('php_version_req', $filters['php_version']);
        }

        $sort = $filters['sort'] ?? 'stars';

        match ($sort) {
            'updated' => $query->orderByDesc('last_commit'),
            'savings' => $query->orderByDesc('monthly_cost_paid'),
            default => $query->orderByDesc('github_stars'),
        };

        return $query->get();
    }

    public function getByCategory(array $filters = []): array
    {
        $alternatives = $this->getFilteredAlternatives($filters);
        $grouped = [];

        foreach (self::CATEGORY_ORDER as $category) {
            $items = $alternatives->where('category', $category)->values();

            if ($items->isNotEmpty()) {
                $grouped[$category] = $items;
            }
        }

        $remaining = $alternatives->whereNotIn('category', self::CATEGORY_ORDER);

        foreach ($remaining->groupBy('category') as $category => $items) {
            $grouped[$category] = $items->values();
        }

        return $grouped;
    }

    public function getComparison(string $slug): ?Alternative
    {
        return Alternative::query()
            ->where('slug', $slug)
            ->first();
    }

    public function getMonthlySavings(Alternative $alternative, int $appCount): float
    {
        return round((float) $alternative->monthly_cost_paid * max($appCount, 1), 2);
    }

    public function getYearlySavings(Alternative $alternative, int $appCount): float
    {
        return round($this->getMonthlySavings($alternative, $appCount) * 12, 2);
    }

    public function categoryLabel(string $category): string
    {
        return self::CATEGORY_LABELS[$category] ?? Str::title($category);
    }

    public function availablePhpVersions(): array
    {
        return Alternative::query()
            ->whereNotNull('php_version_req')
            ->distinct()
            ->orderBy('php_version_req')
            ->pluck('php_version_req')
            ->all();
    }

    public function generateSlug(string $paidTool): string
    {
        return Str::slug($paidTool.'-alternative');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateGithubStats(array $data): ?Alternative
    {
        $alternative = Alternative::query()
            ->where('paid_tool', $data['paid_tool'])
            ->first();

        if ($alternative === null) {
            return null;
        }

        $alternative->update([
            'github_stars' => $data['github_stars'],
            'github_forks' => $data['github_forks'] ?? $alternative->github_forks,
            'last_commit' => $data['last_commit'] ?? null,
            'open_issues' => $data['open_issues'] ?? 0,
            'language' => $data['language'] ?? null,
        ]);

        return $alternative->fresh();
    }
}
