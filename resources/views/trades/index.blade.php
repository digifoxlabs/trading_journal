@extends('layouts.app')

@section('title', 'Trades')

@section('content')
<div
    x-data="tradePriceModal()"
    x-on:keydown.escape.window="close()"
    class="space-y-4"
>
    <div class="page-toolbar">
        <div>
            <p class="text-sm font-medium text-slate-950 dark:text-white">Trade journal</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Calculated PnL, R multiple, flags, and chart screenshots.</p>
        </div>
        <a href="{{ route('trades.create') }}" class="primary-button">New Trade</a>
    </div>

    <div class="table-shell">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Symbol</th>
                    <th class="px-4 py-3 text-left">Dir</th>
                    <th class="px-4 py-3 text-left">Setup</th>
                    <th class="px-4 py-3 text-right">Entry</th>
                    <th class="px-4 py-3 text-right">Exit</th>
                    <th class="px-4 py-3 text-right">Net PnL</th>
                    <th class="px-4 py-3 text-right">R</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($trades as $trade)
                    <tr>
                        <td class="font-medium text-slate-950 dark:text-white">{{ $trade->trade_number }}</td>
                        <td>{{ $trade->trade_date->format('d M Y') }}</td>
                        <td>{{ $trade->symbol->name }}</td>
                        <td class="capitalize">{{ $trade->direction }}</td>
                        <td>{{ $trade->setup_type ?: '-' }}</td>
                        <td class="text-right">{{ number_format($trade->entry_price, 2) }}</td>
                        <td class="text-right">{{ $trade->exit_price ? number_format($trade->exit_price, 2) : '-' }}</td>
                        <td class="text-right font-semibold {{ $trade->net_pnl >= 0 ? '!text-emerald-600' : '!text-rose-600' }}">{{ number_format($trade->net_pnl, 2) }}</td>
                        <td class="text-right">{{ number_format($trade->r_multiple, 2) }}</td>
                        <td class="text-right">
                            <button
                                type="button"
                                class="font-medium text-blue-600 hover:underline dark:text-blue-400"
                                @click="open({
                                    action: @js(route('trades.prices.update', $trade)),
                                    number: @js($trade->trade_number),
                                    symbol: @js($trade->symbol->name),
                                    entryPrice: @js($trade->entry_price),
                                    exitPrice: @js($trade->exit_price),
                                })"
                            >Edit</button>
                            <span class="mx-1 text-slate-300 dark:text-slate-700">/</span>
                            <a href="{{ route('trades.show', $trade) }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Show</a>
                        </td>
                    </tr>
                    @if($trade->images->isNotEmpty())
                        <tr><td colspan="10" class="bg-slate-50 px-4 py-3 dark:bg-slate-950">
                            <div class="flex gap-3 overflow-x-auto">
                                @foreach($trade->images as $image)
                                    <a href="{{ Storage::url($image->image_path) }}" target="_blank" class="shrink-0">
                                        <img src="{{ Storage::url($image->image_path) }}" alt="{{ $image->timeframe }}" class="h-20 w-32 rounded-md object-cover">
                                        <span class="text-xs text-slate-500">{{ $image->timeframe }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </td></tr>
                    @endif
                @empty
                    <tr><td colspan="10" class="px-4 py-10 text-center text-slate-500">No trades yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $trades->links() }}</div>
    </div>

    <div
        x-show="isOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6"
    >
        <div class="absolute inset-0" @click="close()"></div>
        <form
            method="POST"
            :action="trade.action"
            class="admin-panel relative w-full max-w-md p-5 shadow-xl"
        >
            @csrf
            @method('PATCH')

            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-950 dark:text-white">Edit entry and exit</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        <span x-text="trade.number"></span>
                        <span x-show="trade.symbol"> &middot; </span>
                        <span x-text="trade.symbol"></span>
                    </p>
                </div>
                <button
                    type="button"
                    @click="close()"
                    class="icon-button h-9 w-9"
                    aria-label="Close price editor"
                >
                    <span class="text-lg leading-none" aria-hidden="true">&times;</span>
                </button>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="space-y-4">
                <label for="modal_entry_price" class="block">
                    <span class="control-label">Entry Price</span>
                    <input id="modal_entry_price" name="entry_price" type="number" step="0.0001" min="0.0001" x-model="trade.entryPrice" class="control-field" required>
                </label>
                <label for="modal_exit_price" class="block">
                    <span class="control-label">Exit Price</span>
                    <input id="modal_exit_price" name="exit_price" type="number" step="0.0001" min="0.0001" x-model="trade.exitPrice" class="control-field">
                </label>
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button" @click="close()" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
                <button class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700">Save Prices</button>
            </div>
        </form>
    </div>
</div>

<script>
function tradePriceModal() {
    return {
        isOpen: false,
        trade: {
            action: '',
            number: '',
            symbol: '',
            entryPrice: '',
            exitPrice: '',
        },
        open(trade) {
            this.trade = {
                ...trade,
                entryPrice: trade.entryPrice ?? '',
                exitPrice: trade.exitPrice ?? '',
            };
            this.isOpen = true;
            this.$nextTick(() => document.getElementById('modal_entry_price')?.focus());
        },
        close() {
            this.isOpen = false;
        },
    };
}
</script>
@endsection
