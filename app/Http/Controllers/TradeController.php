<?php

namespace App\Http\Controllers;

use App\Http\Requests\TradeRequest;
use App\Models\DailyBias;
use App\Models\Setup;
use App\Models\Symbol;
use App\Models\Trade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TradeController extends Controller
{
    public function index(Request $request): View
    {
        $trades = Trade::with(['symbol', 'images'])
            ->latest('trade_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('trades.index', [
            'trades' => $trades,
            'symbols' => Symbol::orderBy('name')->get(),
            'setups' => Setup::orderBy('name')->get(),
            'timeframes' => $this->timeframes(),
        ]);
    }

    public function create(): View
    {
        return view('trades.create', $this->formOptions(enforceDailyBias: true));
    }

    public function show(Trade $trade): View
    {
        $trade->load(['symbol', 'images']);

        return view('trades.show', [
            'trade' => $trade,
        ]);
    }

    public function edit(Trade $trade): View
    {
        $trade->load(['symbol', 'images']);

        return view('trades.edit', [
            'trade' => $trade,
            ...$this->formOptions(enforceDailyBias: false),
        ]);
    }

    public function store(TradeRequest $request): RedirectResponse
    {
        $data = $this->validatedPayload($request);
        $trade = Trade::create($data);
        $this->storeImages($request, $trade);

        return redirect()->route('trades.show', $trade)->with('status', 'Trade saved.');
    }

    public function update(TradeRequest $request, Trade $trade): RedirectResponse
    {
        $trade->update($this->validatedPayload($request, $trade));
        $this->deleteImages($request, $trade);
        $this->storeImages($request, $trade);

        return redirect()->route('trades.show', $trade)->with('status', 'Trade updated.');
    }

    public function updatePrices(Request $request, Trade $trade): RedirectResponse
    {
        $validated = $request->validate([
            'entry_price' => ['required', 'numeric', 'gt:0'],
            'exit_price' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $trade->update($validated);

        return back()->with('status', 'Trade prices updated.');
    }

    public function destroy(Trade $trade): RedirectResponse
    {
        $trade->delete();

        return redirect()->route('trades.index')->with('status', 'Trade deleted.');
    }

    public function uploadImage(Request $request, Trade $trade): JsonResponse
    {
        $validated = $request->validate([
            'timeframe' => ['required', 'in:1m,5m,15m,1h,4h,1D,1W'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $validated['image']->store('trades', 'public');
        $image = $trade->images()->create([
            'timeframe' => $validated['timeframe'],
            'image_path' => $path,
            'created_at' => now(),
        ]);

        return response()->json([
            'id' => $image->id,
            'url' => Storage::url($path),
            'timeframe' => $image->timeframe,
        ], 201);
    }

    private function validatedPayload(TradeRequest $request, ?Trade $trade = null): array
    {
        $data = $request->validated();

        $data['trade_number'] = $trade?->trade_number ?: $this->nextTradeNumber();
        $data['entry_fees'] = $data['entry_fees'] ?? 0;
        $data['exit_fees'] = $data['exit_fees'] ?? 0;
        $data['mistake_flag'] = $request->boolean('mistake_flag');
        $data['emotion_flag'] = $request->boolean('emotion_flag');

        return $data;
    }

    private function storeImages(Request $request, Trade $trade): void
    {
        $request->validate([
            'image_timeframe.*' => ['nullable', 'in:1m,5m,15m,1h,4h,1D,1W'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ($request->file('images', []) as $index => $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store('trades', 'public');
            $trade->images()->create([
                'timeframe' => $request->input("image_timeframe.$index", '15m'),
                'image_path' => $path,
                'created_at' => now(),
            ]);
        }
    }

    private function deleteImages(Request $request, Trade $trade): void
    {
        $request->validate([
            'delete_image_ids' => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer'],
        ]);

        $images = $trade->images()
            ->whereIn('id', $request->input('delete_image_ids', []))
            ->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }
    }

    private function nextTradeNumber(): string
    {
        return 'TRD-' . now()->format('Ymd') . '-' . str_pad((string) (Trade::withTrashed()->whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
    }

    private function timeframes(): array
    {
        return ['1m', '5m', '15m', '1h', '4h', '1D', '1W'];
    }

    private function formOptions(bool $enforceDailyBias): array
    {
        $dailyBiases = DailyBias::with('symbol')
            ->orderByDesc('date')
            ->get()
            ->map(fn (DailyBias $dailyBias): array => [
                'date' => $dailyBias->date->toDateString(),
                'symbol_id' => $dailyBias->symbol_id,
                'symbol_name' => $dailyBias->symbol->name,
                'bias' => $dailyBias->bias,
                'htf_trend' => $dailyBias->htf_trend,
                'expected_move' => $dailyBias->expected_move,
                'invalidation_level' => $dailyBias->invalidation_level,
            ])
            ->values();

        return [
            'symbols' => ($enforceDailyBias ? Symbol::whereHas('dailyBiases') : Symbol::query())
                ->orderBy('name')
                ->get(),
            'dailyBiases' => $dailyBiases,
            'enforceDailyBias' => $enforceDailyBias,
            'setups' => Setup::orderBy('name')->get(),
            'timeframes' => $this->timeframes(),
        ];
    }
}
