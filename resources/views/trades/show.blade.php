@extends('layouts.app')

@section('title', 'Trade Details')

@section('content')
@php
    $stats = [
        ['Entry', $trade->entry_price],
        ['Stop Loss', $trade->stop_loss],
        ['Target', $trade->target_price],
        ['Exit', $trade->exit_price],
        ['Position Size', $trade->position_size],
        ['Entry Fees', $trade->entry_fees],
        ['Exit Fees', $trade->exit_fees],
        ['Gross PnL', $trade->gross_pnl],
        ['Net PnL', $trade->net_pnl],
        ['Net PnL %', $trade->net_pnl_percent],
        ['R Multiple', $trade->r_multiple],
        ['Result', ucfirst($trade->result)],
    ];
@endphp

<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $trade->trade_number }}</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $trade->symbol->name }} {{ ucfirst($trade->direction) }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $trade->trade_date->format('d M Y') }} · {{ $trade->setup_type ?: 'Unspecified setup' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('trades.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Back</a>
            <a href="{{ route('trades.edit', $trade) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700">Edit</a>
            <form method="POST" action="{{ route('trades.destroy', $trade) }}" onsubmit="return confirm('Delete this trade?')">
                @csrf
                @method('DELETE')
                <button class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-rose-600/25 transition hover:bg-rose-700">Delete</button>
            </form>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as [$label, $value])
            <div class="admin-panel p-4">
                <div class="control-label">{{ $label }}</div>
                <div class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">
                    @if(is_numeric($value))
                        {{ number_format((float) $value, 4) }}
                    @else
                        {{ $value ?: '-' }}
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-[1fr_20rem]">
        <div class="admin-panel p-5">
            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Notes</h3>
            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $trade->notes ?: 'No notes added.' }}</p>
        </div>
        <div class="admin-panel p-5">
            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Flags</h3>
            <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <div class="flex items-center justify-between"><span>Mistake</span><span class="font-medium">{{ $trade->mistake_flag ? 'Flagged' : 'Clean' }}</span></div>
                <div class="flex items-center justify-between"><span>Emotion</span><span class="font-medium">{{ $trade->emotion_flag ? 'Flagged' : 'Clean' }}</span></div>
            </div>
        </div>
    </div>

    <div class="admin-panel p-5">
        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Chart images</h3>
        @if($trade->images->isNotEmpty())
            <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($trade->images as $image)
                    <a href="{{ Storage::url($image->image_path) }}" target="_blank" class="block rounded-lg border border-slate-200 p-2 transition hover:border-blue-300 dark:border-slate-800 dark:hover:border-blue-800">
                        <img src="{{ Storage::url($image->image_path) }}" alt="{{ $image->timeframe }}" class="h-40 w-full rounded-md object-cover">
                        <span class="mt-2 block text-xs font-medium text-slate-500">{{ $image->timeframe }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="mt-3 text-sm text-slate-500">No chart images uploaded.</p>
        @endif
    </div>
</div>
@endsection
