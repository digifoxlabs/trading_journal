@extends('layouts.app')

@section('title', 'New Trade')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Create trade</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">New journal entry</h2>
        </div>
        <a href="{{ route('trades.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Back to trades</a>
    </div>

    @include('trades.partials.form', [
        'action' => route('trades.store'),
        'method' => 'POST',
        'submitLabel' => 'Save Trade',
    ])
</div>
@endsection
