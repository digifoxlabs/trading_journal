@extends('layouts.app')

@section('title', 'Edit Trade')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $trade->trade_number }}</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">Edit trade</h2>
        </div>
        <a href="{{ route('trades.show', $trade) }}" class="text-sm font-medium text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Back to trade</a>
    </div>

    @include('trades.partials.form', [
        'action' => route('trades.update', $trade),
        'method' => 'PUT',
        'submitLabel' => 'Update Trade',
    ])
</div>
@endsection
