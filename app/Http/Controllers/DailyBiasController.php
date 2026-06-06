<?php

namespace App\Http\Controllers;

use App\Http\Requests\DailyBiasRequest;
use App\Models\DailyBias;
use App\Models\Symbol;
use App\Models\Trade;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DailyBiasController extends Controller
{
    public function index(): View
    {
        $biases = DailyBias::with('symbol')->latest('date')->paginate(20);
        $this->attachTradeComparisons($biases->getCollection());

        return view('daily-biases.index', [
            'biases' => $biases,
        ]);
    }

    public function create(): View
    {
        return view('daily-biases.create', $this->formOptions());
    }

    public function edit(DailyBias $dailyBias): View
    {
        return view('daily-biases.edit', [
            'dailyBias' => $dailyBias,
            ...$this->formOptions(),
        ]);
    }

    public function store(DailyBiasRequest $request): RedirectResponse
    {
        $data = $this->payload($request);
        DailyBias::updateOrCreate(
            ['date' => $data['date'], 'symbol_id' => $data['symbol_id']],
            $data
        );

        return redirect()->route('daily-biases.index')->with('status', 'Daily bias saved.');
    }

    public function update(DailyBiasRequest $request, DailyBias $dailyBias): RedirectResponse
    {
        $dailyBias->update($this->payload($request));

        return redirect()->route('daily-biases.index')->with('status', 'Daily bias updated.');
    }

    public function destroy(DailyBias $dailyBias): RedirectResponse
    {
        $dailyBias->delete();

        return back()->with('status', 'Daily bias deleted.');
    }

    private function payload(DailyBiasRequest $request): array
    {
        return $request->validated();
    }

    private function formOptions(): array
    {
        return [
            'symbols' => Symbol::orderBy('name')->get(),
        ];
    }

    private function attachTradeComparisons($biases): void
    {
        if ($biases->isEmpty()) {
            return;
        }

        $dates = $biases->pluck('date')->map->toDateString()->unique()->values();
        $symbolIds = $biases->pluck('symbol_id')->unique()->values();

        $trades = Trade::with('symbol')
            ->whereIn('trade_date', $dates)
            ->whereIn('symbol_id', $symbolIds)
            ->orderBy('trade_date')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Trade $trade) => $trade->trade_date->toDateString() . '|' . $trade->symbol_id);

        $biases->each(function (DailyBias $bias) use ($trades): void {
            $matchingTrades = $trades->get($bias->date->toDateString() . '|' . $bias->symbol_id, collect())
                ->map(function (Trade $trade) use ($bias) {
                    $trade->setAttribute('aligned_with_bias', $this->tradeAlignedWithBias($trade, $bias->bias));

                    return $trade;
                });

            $bias->setRelation('tradesForDay', $matchingTrades);
            $bias->setAttribute('aligned_trades_count', $matchingTrades->where('aligned_with_bias', true)->count());
            $bias->setAttribute('total_trades_count', $matchingTrades->count());
        });
    }

    private function tradeAlignedWithBias(Trade $trade, string $bias): bool
    {
        return match ($bias) {
            'bullish' => $trade->direction === 'long',
            'bearish' => $trade->direction === 'short',
            default => false,
        };
    }
}
