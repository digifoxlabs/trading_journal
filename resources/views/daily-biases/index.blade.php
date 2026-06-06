@extends('layouts.app')

@section('title', 'Daily Bias')

@section('content')
<div class="space-y-4">
    <div class="page-toolbar">
        <div>
            <p class="text-sm font-medium text-slate-950 dark:text-white">Daily bias log</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review planned bias and compare it with executed trades.</p>
        </div>
        <a href="{{ route('daily-biases.create') }}" class="primary-button">Create Bias</a>
    </div>

    <div class="table-shell" x-data="{ openTrades: null, modal: null }">
        <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Symbol</th>
                    <th class="px-4 py-3 text-left">Bias</th>
                    <th class="px-4 py-3 text-left">HTF</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($biases as $bias)
                <tr>
                    <td>{{ $bias->date->format('d M Y') }}</td>
                    <td class="font-medium text-slate-950 dark:text-white">{{ $bias->symbol->name }}</td>
                    <td class="capitalize">{{ $bias->bias }}</td>
                    <td>{{ $bias->htf_trend ?: '-' }}</td>
                    <td class="text-right">
                        <div class="flex justify-end gap-3">
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300" @click.prevent="modal = {{ $bias->id }}">View</a>
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300" @click.prevent="openTrades = openTrades === {{ $bias->id }} ? null : {{ $bias->id }}" x-text="openTrades === {{ $bias->id }} ? 'Hide Trades' : 'Trades'">Trades</a>
                            <a href="{{ route('daily-biases.edit', $bias) }}" class="font-medium text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Edit</a>
                        </div>
                    </td>
                </tr>
                <tr x-show="openTrades === {{ $bias->id }}" x-cloak>
                    <td colspan="5" class="bg-slate-50 px-4 py-4 dark:bg-slate-950/50">
                        <div class="grid gap-4 lg:grid-cols-[16rem_1fr]">
                            <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                                <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Alignment</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">
                                    {{ $bias->aligned_trades_count }} / {{ $bias->total_trades_count }}
                                </div>
                                <div class="mt-1 text-sm text-slate-500">
                                    trades aligned with the {{ $bias->bias }} bias
                                </div>
                                @if($bias->tradesForDay->isEmpty())
                                    <form method="POST" action="{{ route('daily-biases.destroy', $bias) }}" class="mt-4">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </form>
                                @endif
                            </div>

                            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                                @if($bias->tradesForDay->isNotEmpty())
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-800">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Trade</th>
                                                <th class="px-3 py-2 text-left">Direction</th>
                                                <th class="px-3 py-2 text-left">Result</th>
                                                <th class="px-3 py-2 text-right">Net P/L</th>
                                                <th class="px-3 py-2 text-left">Bias Match</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            @foreach($bias->tradesForDay as $trade)
                                                <tr>
                                                    <td class="px-3 py-2">
                                                        <a href="{{ route('trades.show', $trade) }}" class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">{{ $trade->trade_number }}</a>
                                                    </td>
                                                    <td class="px-3 py-2 capitalize">{{ $trade->direction }}</td>
                                                    <td class="px-3 py-2 capitalize">{{ $trade->result }}</td>
                                                    <td class="px-3 py-2 text-right">{{ number_format((float) $trade->net_pnl, 2) }}</td>
                                                    <td class="px-3 py-2">
                                                        <span class="rounded-md px-2 py-1 text-xs font-semibold {{ $trade->aligned_with_bias ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' }}">
                                                            {{ $trade->aligned_with_bias ? 'Aligned' : 'Not aligned' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="p-4 text-sm text-slate-500">No trades were taken for this date and symbol.</div>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                <td colspan="5" class="px-4 py-6 text-center text-slate-500">No daily biases found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
        </div>
        <div class="pagination-wrap">{{ $biases->links() }}</div>

        @foreach($biases as $bias)
            <div x-show="modal === {{ $bias->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" @keydown.escape.window="modal = null">
                <div class="w-full max-w-2xl rounded-lg border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900" @click.outside="modal = null">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $bias->date->format('d M Y') }}</div>
                            <h2 class="mt-1 text-lg font-semibold text-slate-950 dark:text-white">{{ $bias->symbol->name }} Daily Bias</h2>
                        </div>
                        <button type="button" class="icon-button" @click="modal = null" aria-label="Close bias details">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4 p-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <div class="control-label">Bias</div>
                                <div class="mt-1 capitalize text-slate-950 dark:text-white">{{ $bias->bias }}</div>
                            </div>
                            <div>
                                <div class="control-label">HTF Trend</div>
                                <div class="mt-1 text-slate-950 dark:text-white">{{ $bias->htf_trend ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="control-label">Expected Move</div>
                                <div class="mt-1 text-slate-950 dark:text-white">{{ $bias->expected_move ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="control-label">Invalidation Level</div>
                                <div class="mt-1 text-slate-950 dark:text-white">{{ $bias->invalidation_level ?: '-' }}</div>
                            </div>
                        </div>

                        <div>
                            <div class="control-label">Key Levels</div>
                            <div class="mt-1 whitespace-pre-line rounded-md bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-950 dark:text-slate-200">{{ $bias->key_levels ?: '-' }}</div>
                        </div>

                        <div>
                            <div class="control-label">Notes</div>
                            <div class="mt-1 whitespace-pre-line rounded-md bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-950 dark:text-slate-200">{{ $bias->notes ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
