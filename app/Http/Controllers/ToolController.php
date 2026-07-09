<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToolFilterRequest;
use App\Services\AlternativeService;
use App\Services\SeoService;
use Illuminate\View\View;

class ToolController extends Controller
{
    public function __construct(
        private readonly AlternativeService $alternatives,
        private readonly SeoService $seo
    ) {}

    public function index(ToolFilterRequest $request): View
    {
        $filters = $request->validated();

        return view('tools.index', [
            'groupedAlternatives' => $this->alternatives->getByCategory($filters),
            'filters' => $filters,
            'phpVersions' => $this->alternatives->availablePhpVersions(),
            'categoryLabels' => AlternativeService::CATEGORY_LABELS,
            'seo' => $this->seo->getToolsIndexMeta(),
        ]);
    }

    public function show(string $slug): View
    {
        $alternative = $this->alternatives->getComparison($slug);

        abort_if($alternative === null, 404);

        $appCount = max((int) request()->query('apps', 1), 1);

        return view('tools.show', [
            'alternative' => $alternative,
            'appCount' => $appCount,
            'monthlyCost' => $this->alternatives->getMonthlySavings($alternative, $appCount),
            'yearlyCost' => $this->alternatives->getYearlySavings($alternative, $appCount),
            'seo' => $this->seo->getToolMeta($alternative),
        ]);
    }
}
