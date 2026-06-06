@extends('layouts.app')

@section('title', 'Symbols')

@section('content')
<div
    x-data="{ modal: @js($errors->any() ? old('_symbol_modal', 'create') : null) }"
    class="space-y-5"
>
    <div class="page-toolbar">
        <div>
            <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Symbols</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage markets used in trades and bias plans.</p>
        </div>
        <button type="button" class="primary-button" @click="modal = 'create'">Create Symbol</button>
    </div>

    <div class="table-shell">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-left">Name</th>
                        <th class="text-left">Exchange</th>
                        <th class="text-left">Type</th>
                        <th class="text-right">Tick</th>
                        <th class="text-right">Lot</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($symbols as $symbol)
                        <tr>
                            <td class="font-medium text-slate-950 dark:text-white">{{ $symbol->name }}</td>
                            <td>{{ $symbol->exchange ?: '-' }}</td>
                            <td>{{ ucfirst($symbol->instrument_type) }}</td>
                            <td class="text-right">{{ $symbol->tick_size }}</td>
                            <td class="text-right">{{ $symbol->lot_size }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-3">
                                    <button
                                        type="button"
                                        class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                        @click="modal = 'edit-{{ $symbol->id }}'"
                                    >
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('symbols.destroy', $symbol) }}" onsubmit="return confirm('Delete this symbol?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No symbols found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $symbols->links() }}</div>
    </div>

    @include('symbols.partials.modal', [
        'modalId' => 'create',
        'title' => 'Create symbol',
        'description' => 'Add a market you use in trades and bias plans.',
        'action' => route('symbols.store'),
        'method' => 'POST',
        'symbol' => null,
        'submitLabel' => 'Create Symbol',
    ])

    @foreach($symbols as $symbol)
        @include('symbols.partials.modal', [
            'modalId' => 'edit-' . $symbol->id,
            'title' => 'Edit symbol',
            'description' => 'Update the market details used across your journal.',
            'action' => route('symbols.update', $symbol),
            'method' => 'PUT',
            'symbol' => $symbol,
            'submitLabel' => 'Save Changes',
        ])
    @endforeach
</div>
@endsection
