<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalculateRequest;
use App\Models\Page;
use App\Services\CalculatorService;
use App\Services\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CalculatorController extends Controller
{
    public function __construct(
        private readonly CalculatorService $calculator,
        private readonly SeoService $seo
    ) {}

    public function index(): View
    {
        return view('calculator.index', [
            'rates' => $this->calculator->getRatesForFrontend(),
            'footerPage' => Page::query()->where('slug', 'calculator-footer')->first(),
            'defaults' => [
                'amount' => (float) request()->query('amount', 1000),
                'platform' => request()->query('platform', 'upwork'),
                'processor' => request()->query('processor', 'payoneer'),
                'currency' => request()->query('currency', 'USD'),
            ],
            'seo' => $this->seo->getCalculatorMeta('default'),
            'heading' => 'Freelancer Fee Calculator',
        ]);
    }

    public function calculate(CalculateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->calculator->calculate(
            (float) $validated['amount'],
            $validated['platform'],
            $validated['processor'],
            $validated['currency']
        );

        Log::info('calculator.calculate', array_merge($validated, [
            'net_usd' => $result['net_usd'],
            'net_pkr' => $result['net_pkr'],
        ]));

        return response()->json($result);
    }

    public function variant(string $slug): View
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('tool_type', 'calculator')
            ->firstOrFail();

        $content = $page->content;

        return view('calculator.variant', [
            'rates' => $this->calculator->getRatesForFrontend(),
            'footerPage' => Page::query()->where('slug', 'calculator-footer')->first(),
            'page' => $page,
            'defaults' => $content['defaults'] ?? [
                'amount' => 1000,
                'platform' => 'upwork',
                'processor' => 'payoneer',
                'currency' => 'USD',
            ],
            'seo' => $this->seo->getCalculatorMeta($slug),
            'heading' => $content['h1'] ?? $page->title,
        ]);
    }
}
