@extends('layouts.app')

@section('title', 'Edit Daily Bias')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $dailyBias->date->format('d M Y') }}</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">Edit daily bias</h2>
        </div>
        <a href="{{ route('daily-biases.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Back to daily bias</a>
    </div>

    @include('daily-biases.partials.form', [
        'dailyBias' => $dailyBias,
        'action' => route('daily-biases.update', $dailyBias),
        'method' => 'PUT',
        'submitLabel' => 'Update Bias',
    ])
</div>
@endsection
