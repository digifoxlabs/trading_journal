<?php

namespace App\Http\Controllers;

use App\Models\DailyBias;
use App\Models\Setup;
use App\Models\Symbol;
use App\Models\Trade;
use App\Services\PerformanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly PerformanceService $performance)
    {
    }

    public function index(Request $request): View
    {
        if ($request->routeIs('analytics')) {
            return view('dashboard.analytics', [
                'symbols' => Symbol::orderBy('name')->get(),
                'setups' => Setup::orderBy('name')->get(),
                'payload' => $this->payload($request),
            ]);
        }

        $today = today();
        $symbols = Symbol::orderBy('name')->get();
        $todayBiases = DailyBias::with('symbol')
            ->whereDate('date', $today)
            ->orderBy('symbol_id')
            ->get();

        $biasedSymbolIds = $todayBiases->pluck('symbol_id');

        return view('dashboard.index', [
            'today' => $today,
            'symbols' => $symbols,
            'todayBiases' => $todayBiases,
            'symbolsWithoutBias' => $symbols->whereNotIn('id', $biasedSymbolIds)->values(),
            'todayTrades' => Trade::with('symbol')
                ->whereDate('trade_date', $today)
                ->latest('id')
                ->get(),
            'openSwingTrades' => Trade::with('symbol')
                ->whereDate('trade_date', '<', $today)
                ->whereNull('exit_price')
                ->latest('trade_date')
                ->latest('id')
                ->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json($this->payload($request));
    }

    private function payload(Request $request): array
    {
        $trades = $this->performance->queryWithFilters($request->all())->get();

        return [
            'metrics' => $this->performance->summarize($trades),
            'charts' => $this->performance->chartData($trades),
        ];
    }
}
