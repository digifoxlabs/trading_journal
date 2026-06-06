<?php

namespace App\Services;

use App\Models\MonthlyPerformance;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PerformanceService
{
    public function calculateTradeMetrics(array $trade, float $accountBalance = 100000): array
    {
        $entry = (float) ($trade['entry_price'] ?? 0);
        $stop = (float) ($trade['stop_loss'] ?? 0);
        $exit = (float) ($trade['exit_price'] ?? 0);
        $size = (float) ($trade['position_size'] ?? 0);
        $entryFees = (float) ($trade['entry_fees'] ?? 0);
        $exitFees = (float) ($trade['exit_fees'] ?? 0);
        $direction = ($trade['direction'] ?? 'long') === 'short' ? -1 : 1;

        if ($exit <= 0) {
            return [
                'gross_pnl' => 0,
                'net_pnl' => -($entryFees + $exitFees),
                'net_pnl_percent' => $accountBalance > 0 ? (-(($entryFees + $exitFees)) / $accountBalance) * 100 : 0,
                'r_multiple' => 0,
                'result' => 'open',
            ];
        }

        $gross = $size * ($exit - $entry) * $direction;
        $net = $gross - $entryFees - $exitFees;
        $risk = abs($entry - $stop);
        $reward = abs($exit - $entry);

        return [
            'gross_pnl' => round($gross, 4),
            'net_pnl' => round($net, 4),
            'net_pnl_percent' => $accountBalance > 0 ? round(($net / $accountBalance) * 100, 4) : 0,
            'r_multiple' => $risk > 0 ? round($reward / $risk, 4) : 0,
            'result' => $net > 0 ? 'win' : ($net < 0 ? 'loss' : 'breakeven'),
        ];
    }

    public function dateRange(string $period = 'this_month', ?string $start = null, ?string $end = null): array
    {
        $today = now();

        return match ($period) {
            'today' => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            'last_7_days' => [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'last_quarter' => [$today->copy()->subQuarter()->startOfQuarter(), $today->copy()->subQuarter()->endOfQuarter()],
            'current_financial_year' => [$today->month >= 4 ? Carbon::create($today->year, 4, 1) : Carbon::create($today->year - 1, 4, 1), $today->month >= 4 ? Carbon::create($today->year + 1, 3, 31)->endOfDay() : Carbon::create($today->year, 3, 31)->endOfDay()],
            'last_financial_year' => [$today->month >= 4 ? Carbon::create($today->year - 1, 4, 1) : Carbon::create($today->year - 2, 4, 1), $today->month >= 4 ? Carbon::create($today->year, 3, 31)->endOfDay() : Carbon::create($today->year - 1, 3, 31)->endOfDay()],
            'custom' => [Carbon::parse($start ?: $today)->startOfDay(), Carbon::parse($end ?: $today)->endOfDay()],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };
    }

    public function queryWithFilters(array $filters = []): Builder
    {
        [$start, $end] = $this->dateRange($filters['period'] ?? 'this_month', $filters['start_date'] ?? null, $filters['end_date'] ?? null);

        return Trade::query()
            ->with('symbol')
            ->whereBetween('trade_date', [$start->toDateString(), $end->toDateString()])
            ->when($filters['symbol_id'] ?? null, fn (Builder $query, $value) => $query->where('symbol_id', $value))
            ->when($filters['setup_type'] ?? null, fn (Builder $query, $value) => $query->where('setup_type', $value))
            ->when($filters['direction'] ?? null, fn (Builder $query, $value) => $query->where('direction', $value))
            ->when(isset($filters['mistake_flag']) && $filters['mistake_flag'] !== '', fn (Builder $query) => $query->where('mistake_flag', (bool) $filters['mistake_flag']))
            ->when(isset($filters['emotion_flag']) && $filters['emotion_flag'] !== '', fn (Builder $query) => $query->where('emotion_flag', (bool) $filters['emotion_flag']))
            ->when($filters['timeframe'] ?? null, fn (Builder $query, $value) => $query->whereHas('images', fn (Builder $images) => $images->where('timeframe', $value)))
            ->orderBy('trade_date')
            ->orderBy('id');
    }

    public function summarize(Collection $trades): array
    {
        $closed = $trades->whereIn('result', ['win', 'loss', 'breakeven']);
        $wins = $closed->where('net_pnl', '>', 0);
        $losses = $closed->where('net_pnl', '<', 0);
        $total = $closed->count();
        $winRate = $total > 0 ? $wins->count() / $total : 0;
        $lossRate = $total > 0 ? $losses->count() / $total : 0;
        $avgWin = (float) $wins->avg('net_pnl');
        $avgLoss = abs((float) $losses->avg('net_pnl'));
        $grossProfit = (float) $wins->sum('net_pnl');
        $grossLoss = abs((float) $losses->sum('net_pnl'));

        return [
            'total_trades' => $trades->count(),
            'winning_trades' => $wins->count(),
            'losing_trades' => $losses->count(),
            'win_rate' => round($winRate * 100, 2),
            'loss_rate' => round($lossRate * 100, 2),
            'profit_factor' => $grossLoss > 0 ? round($grossProfit / $grossLoss, 2) : ($grossProfit > 0 ? round($grossProfit, 2) : 0),
            'expectancy' => round(($winRate * $avgWin) - ($lossRate * $avgLoss), 2),
            'avg_win' => round($avgWin, 2),
            'avg_loss' => round($avgLoss, 2),
            'average_r' => round((float) $closed->avg('r_multiple'), 2),
            'largest_win' => round((float) $wins->max('net_pnl'), 2),
            'largest_loss' => round((float) $losses->min('net_pnl'), 2),
            'max_drawdown' => round($this->maxDrawdown($trades), 2),
            'max_consecutive_wins' => $this->maxStreak($closed, true),
            'max_consecutive_losses' => $this->maxStreak($closed, false),
            'net_pnl' => round((float) $trades->sum('net_pnl'), 2),
        ];
    }

    public function chartData(Collection $trades): array
    {
        $equity = [];
        $running = 0;
        foreach ($trades->values() as $index => $trade) {
            $running += (float) $trade->net_pnl;
            $equity[] = ['x' => $index + 1, 'y' => round($running, 2)];
        }

        $monthly = $trades->groupBy(fn ($trade) => $trade->trade_date->format('Y-m'))
            ->map(fn ($group) => round((float) $group->sum('net_pnl'), 2));

        $setup = $trades->groupBy(fn ($trade) => $trade->setup_type ?: 'Unspecified')
            ->map(fn ($group) => [
                'wins' => $group->where('net_pnl', '>', 0)->count(),
                'losses' => $group->where('net_pnl', '<', 0)->count(),
                'pnl' => round((float) $group->sum('net_pnl'), 2),
            ]);

        $symbols = $trades->groupBy(fn ($trade) => $trade->symbol?->name ?: 'Unknown')
            ->map(fn ($group) => round((float) $group->sum('net_pnl'), 2));

        return [
            'equity' => $equity,
            'monthly' => ['labels' => $monthly->keys()->values(), 'values' => $monthly->values()],
            'win_ratio' => [
                'labels' => ['Wins', 'Losses', 'Breakeven/Open'],
                'values' => [
                    $trades->where('net_pnl', '>', 0)->count(),
                    $trades->where('net_pnl', '<', 0)->count(),
                    $trades->where('net_pnl', '=', 0)->count(),
                ],
            ],
            'setup' => ['labels' => $setup->keys()->values(), 'wins' => $setup->pluck('wins')->values(), 'losses' => $setup->pluck('losses')->values(), 'pnl' => $setup->pluck('pnl')->values()],
            'symbols' => ['labels' => $symbols->keys()->values(), 'values' => $symbols->values()],
        ];
    }

    public function syncMonthlyPerformance(): void
    {
        $groups = Trade::query()
            ->with('symbol')
            ->orderBy('trade_date')
            ->get()
            ->groupBy(fn (Trade $trade) => $trade->trade_date->format('Y-m'));

        MonthlyPerformance::query()->delete();

        foreach ($groups as $key => $trades) {
            [$year, $month] = array_map('intval', explode('-', $key));
            $metrics = $this->summarize($trades);

            MonthlyPerformance::create([
                'month' => $month,
                'year' => $year,
                'total_trades' => $metrics['total_trades'],
                'winning_trades' => $metrics['winning_trades'],
                'losing_trades' => $metrics['losing_trades'],
                'net_pnl' => $metrics['net_pnl'],
                'avg_r_multiple' => $metrics['average_r'],
                'win_rate' => $metrics['win_rate'],
                'profit_factor' => $metrics['profit_factor'],
                'max_drawdown' => $metrics['max_drawdown'],
            ]);
        }
    }

    private function maxDrawdown(Collection $trades): float
    {
        $peak = 0;
        $running = 0;
        $maxDrawdown = 0;

        foreach ($trades as $trade) {
            $running += (float) $trade->net_pnl;
            $peak = max($peak, $running);
            $maxDrawdown = min($maxDrawdown, $running - $peak);
        }

        return abs($maxDrawdown);
    }

    private function maxStreak(Collection $trades, bool $wins): int
    {
        $max = 0;
        $current = 0;

        foreach ($trades as $trade) {
            $matched = $wins ? $trade->net_pnl > 0 : $trade->net_pnl < 0;
            $current = $matched ? $current + 1 : 0;
            $max = max($max, $current);
        }

        return $max;
    }
}
