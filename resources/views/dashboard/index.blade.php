@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $todayLabel = $today->format('d M Y');
    $nextBiasParams = ['date' => $today->toDateString()];
    if ($symbolsWithoutBias->isNotEmpty()) {
        $nextBiasParams['symbol_id'] = $symbolsWithoutBias->first()->id;
    }
@endphp

<div
    x-data="dashboardExitModal()"
    x-on:keydown.escape.window="closeExitModal()"
    class="space-y-5"
>
    <div class="admin-panel overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-950 px-5 py-5 text-white dark:border-slate-800">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-200">Session command centre</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight">Today&apos;s trading flow</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Start with bias, punch trades only when the plan exists, and keep open swing positions visible until the exit is captured.</p>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center sm:min-w-[25rem]">
                    <div class="rounded-md border border-white/10 bg-white/10 px-3 py-3">
                        <div class="text-xs text-slate-300">Date</div>
                        <div class="mt-1 text-sm font-semibold">{{ $todayLabel }}</div>
                    </div>
                    <div class="rounded-md border border-white/10 bg-white/10 px-3 py-3">
                        <div class="text-xs text-slate-300">Biases</div>
                        <div class="mt-1 text-sm font-semibold">{{ $todayBiases->count() }}</div>
                    </div>
                    <div class="rounded-md border border-white/10 bg-white/10 px-3 py-3">
                        <div class="text-xs text-slate-300">Open swings</div>
                        <div class="mt-1 text-sm font-semibold">{{ $openSwingTrades->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-0 divide-y divide-slate-200 dark:divide-slate-800 lg:grid-cols-2 lg:divide-x lg:divide-y-0">
            <div class="p-5">
                <div class="flex h-full flex-col justify-between gap-5">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3"/></svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Daily bias</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $todayBiases->isEmpty() ? 'No bias started for today.' : 'Today has active symbol bias.' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            @forelse($todayBiases as $bias)
                                <div class="grid gap-3 rounded-md border border-slate-200 px-3 py-3 dark:border-slate-800 sm:grid-cols-[1fr_auto] sm:items-center">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="text-sm font-semibold text-slate-950 dark:text-white">{{ $bias->symbol->name }}</div>
                                            <span class="rounded-md px-2 py-1 text-xs font-semibold capitalize {{ ['bullish' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300', 'bearish' => 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300'][$bias->bias] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">{{ $bias->bias }}</span>
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $bias->expected_move ?: 'Move not defined' }}</div>
                                    </div>
                                    <a href="{{ route('trades.create', ['trade_date' => $today->toDateString(), 'symbol_id' => $bias->symbol_id]) }}" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700">Punch Trade</a>
                                </div>
                            @empty
                                <div class="rounded-md border border-dashed border-slate-300 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">Create the first bias before the trade tape starts.</div>
                            @endforelse
                        </div>
                    </div>

                    <a href="{{ route('daily-biases.create', $nextBiasParams) }}" class="primary-button w-full">
                        {{ $todayBiases->isEmpty() ? "Start today's bias" : 'Add another symbol bias' }}
                    </a>
                </div>
            </div>

            <div class="p-5">
                <div class="flex h-full flex-col justify-between gap-5">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.8l2 2L16 9m5 3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Next symbol</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Bias coverage still available today.</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse($symbolsWithoutBias->take(8) as $symbol)
                                <span class="rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 dark:border-slate-800 dark:text-slate-200">{{ $symbol->name }}</span>
                            @empty
                                <span class="rounded-md bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">All tracked symbols have a bias today.</span>
                            @endforelse
                        </div>
                    </div>

                    <a href="{{ route('daily-biases.create', $nextBiasParams) }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        Make bias for another symbol
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <div class="admin-panel overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Trades taken today</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Current session execution tape.</p>
                </div>
                <a href="{{ route('trades.index') }}" class="secondary-link text-sm">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-left">Trade</th>
                            <th class="text-left">Symbol</th>
                            <th class="text-left">Direction</th>
                            <th class="text-right">Entry</th>
                            <th class="text-right">Exit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayTrades as $trade)
                            <tr>
                                <td class="font-medium text-slate-950 dark:text-white"><a href="{{ route('trades.show', $trade) }}" class="hover:underline">{{ $trade->trade_number }}</a></td>
                                <td>{{ $trade->symbol->name }}</td>
                                <td class="capitalize">{{ $trade->direction }}</td>
                                <td class="text-right">{{ number_format($trade->entry_price, 2) }}</td>
                                <td class="text-right">
                                    @if($trade->exit_price)
                                        {{ number_format($trade->exit_price, 2) }}
                                    @else
                                        <button
                                            type="button"
                                            class="font-medium text-blue-600 hover:underline dark:text-blue-400"
                                            @click="openExitModal({
                                                action: @js(route('trades.prices.update', $trade)),
                                                number: @js($trade->trade_number),
                                                symbol: @js($trade->symbol->name),
                                                entryPrice: @js($trade->entry_price),
                                                exitPrice: @js($trade->exit_price),
                                            })"
                                        >Open</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No trades punched today.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-panel overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Open swing trades</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Older trades waiting for exit price.</p>
                </div>
                <a href="{{ route('trades.index') }}" class="secondary-link text-sm">Manage</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-left">Date</th>
                            <th class="text-left">Symbol</th>
                            <th class="text-left">Direction</th>
                            <th class="text-right">Entry</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($openSwingTrades as $trade)
                            <tr>
                                <td>{{ $trade->trade_date->format('d M') }}</td>
                                <td class="font-medium text-slate-950 dark:text-white">{{ $trade->symbol->name }}</td>
                                <td class="capitalize">{{ $trade->direction }}</td>
                                <td class="text-right">{{ number_format($trade->entry_price, 2) }}</td>
                                <td class="text-right">
                                    <button
                                        type="button"
                                        class="font-medium text-blue-600 hover:underline dark:text-blue-400"
                                        @click="openExitModal({
                                            action: @js(route('trades.prices.update', $trade)),
                                            number: @js($trade->trade_number),
                                            symbol: @js($trade->symbol->name),
                                            entryPrice: @js($trade->entry_price),
                                            exitPrice: @js($trade->exit_price),
                                        })"
                                    >Add exit</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No carried swing trades need an exit.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div
        x-show="exitModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6"
    >
        <div class="absolute inset-0" @click="closeExitModal()"></div>
        <form
            method="POST"
            :action="exitTrade.action"
            class="admin-panel relative w-full max-w-md p-5 shadow-xl"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="entry_price" x-model="exitTrade.entryPrice">

            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-950 dark:text-white">Add exit price</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        <span x-text="exitTrade.number"></span>
                        <span x-show="exitTrade.symbol"> &middot; </span>
                        <span x-text="exitTrade.symbol"></span>
                    </p>
                </div>
                <button
                    type="button"
                    @click="closeExitModal()"
                    class="icon-button h-9 w-9"
                    aria-label="Close exit price editor"
                >
                    <span class="text-lg leading-none" aria-hidden="true">&times;</span>
                </button>
            </div>

            <label for="dashboard_exit_price" class="block">
                <span class="control-label">Exit Price</span>
                <input id="dashboard_exit_price" name="exit_price" type="number" step="0.0001" min="0.0001" x-model="exitTrade.exitPrice" class="control-field" required>
            </label>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button" @click="closeExitModal()" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
                <button class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700">Save Exit</button>
            </div>
        </form>
    </div>
</div>

<script>
function dashboardExitModal() {
    return {
        exitModalOpen: false,
        exitTrade: {
            action: '',
            number: '',
            symbol: '',
            entryPrice: '',
            exitPrice: '',
        },
        openExitModal(trade) {
            this.exitTrade = {
                ...trade,
                entryPrice: trade.entryPrice ?? '',
                exitPrice: trade.exitPrice ?? '',
            };
            this.exitModalOpen = true;
            this.$nextTick(() => document.getElementById('dashboard_exit_price')?.focus());
        },
        closeExitModal() {
            this.exitModalOpen = false;
        },
    };
}
</script>
@endsection
