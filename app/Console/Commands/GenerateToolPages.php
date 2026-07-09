<?php

namespace App\Console\Commands;

use App\Models\Alternative;
use App\Models\Page;
use App\Services\AlternativeService;
use Illuminate\Console\Command;

class GenerateToolPages extends Command
{
    protected $signature = 'tools:generate-pages';

    protected $description = 'Ensure each alternative has a matching slug and pages table entry';

    public function handle(AlternativeService $alternatives): int
    {
        $count = 0;

        Alternative::query()->each(function (Alternative $alternative) use ($alternatives, &$count) {
            $slug = $alternatives->generateSlug($alternative->paid_tool);

            if ($alternative->slug !== $slug) {
                $alternative->slug = $slug;
                $alternative->save();
            }

            Page::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $alternative->paid_tool.' Alternative: '.$alternative->open_tool.' | DevRates',
                    'meta_description' => 'Replace '.$alternative->paid_tool.' with '.$alternative->open_tool.'. Save $'.number_format($alternative->monthly_cost_paid, 0).'/mo with this self-hosted Laravel-compatible alternative.',
                    'tool_type' => 'alternative',
                    'published_at' => now(),
                    'content_json' => [
                        'paid_tool' => $alternative->paid_tool,
                        'open_tool' => $alternative->open_tool,
                        'alternative_id' => $alternative->id,
                    ],
                ]
            );

            $count++;
        });

        $this->info("Generated or updated {$count} alternative pages.");

        return self::SUCCESS;
    }
}
