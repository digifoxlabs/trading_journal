@php
    $instrumentType = old('instrument_type', $symbol?->instrument_type ?? 'crypto');
    $types = [
        'crypto' => 'Crypto',
        'equity' => 'Equity',
        'futures' => 'Futures',
        'option' => 'Option',
        'forex' => 'Forex',
    ];
@endphp

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <input type="hidden" name="_symbol_modal" value="{{ $modalId }}">

    <x-input label="Name" name="name" value="{{ $symbol?->name }}" required />
    <x-input label="Exchange" name="exchange" value="{{ $symbol?->exchange }}" />

    <x-select label="Instrument Type" name="instrument_type">
        @foreach($types as $value => $label)
            <option value="{{ $value }}" @selected($instrumentType === $value)>{{ $label }}</option>
        @endforeach
    </x-select>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-input label="Tick Size" name="tick_size" type="number" step="0.0001" value="{{ $symbol?->tick_size ?? '0.0001' }}" required />
        <x-input label="Lot Size" name="lot_size" type="number" step="0.0001" value="{{ $symbol?->lot_size ?? '1' }}" required />
    </div>

    <div class="flex justify-end gap-3 pt-1">
        <button type="button" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="modal = null">Cancel</button>
        <button class="primary-button">{{ $submitLabel }}</button>
    </div>
</form>
