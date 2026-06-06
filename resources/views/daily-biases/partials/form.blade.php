@php
    $dailyBias ??= null;
    $method ??= 'POST';
    $submitLabel ??= 'Save Bias';
@endphp

<form method="POST" action="{{ $action }}" class="admin-panel space-y-4 p-5">
    @csrf
    @if(! in_array(strtoupper($method), ['GET', 'POST'], true))
        @method($method)
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <x-input label="Date" name="date" type="date" value="{{ old('date', optional($dailyBias?->date)->toDateString() ?? request('date', now()->toDateString())) }}" required />
        <x-select label="Symbol" name="symbol_id" required>
            <option value="">Select symbol</option>
            @foreach($symbols as $symbol)
                <option value="{{ $symbol->id }}" @selected((string) old('symbol_id', $dailyBias?->symbol_id ?? request('symbol_id')) === (string) $symbol->id)>{{ $symbol->name }}</option>
            @endforeach
        </x-select>
        <x-select label="Bias" name="bias" required>
            <option value="bullish" @selected(old('bias', $dailyBias?->bias ?? 'bullish') === 'bullish')>Bullish</option>
            <option value="bearish" @selected(old('bias', $dailyBias?->bias) === 'bearish')>Bearish</option>
            <option value="neutral" @selected(old('bias', $dailyBias?->bias) === 'neutral')>Neutral</option>
        </x-select>
        <x-input label="HTF Trend" name="htf_trend" value="{{ old('htf_trend', $dailyBias?->htf_trend) }}" />
        <x-input label="Expected Move" name="expected_move" value="{{ old('expected_move', $dailyBias?->expected_move) }}" />
        <x-input label="Invalidation Level" name="invalidation_level" type="number" step="0.0001" value="{{ old('invalidation_level', $dailyBias?->invalidation_level) }}" />
    </div>

    <label class="block">
        <span class="control-label">Key Levels</span>
        <textarea name="key_levels" rows="3" class="control-field">{{ old('key_levels', $dailyBias?->key_levels) }}</textarea>
    </label>

    <label class="block">
        <span class="control-label">Notes</span>
        <textarea name="notes" rows="3" class="control-field">{{ old('notes', $dailyBias?->notes) }}</textarea>
    </label>

    <div class="flex justify-end gap-3">
        <a href="{{ route('daily-biases.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
        <button class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700">{{ $submitLabel }}</button>
    </div>
</form>
