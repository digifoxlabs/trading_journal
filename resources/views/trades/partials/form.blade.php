@php
    $trade ??= null;
    $method ??= 'POST';
    $submitLabel ??= 'Save Trade';
    $entryFees = old('entry_fees', $trade?->entry_fees ?? 0);
    $exitFees = old('exit_fees', $trade?->exit_fees ?? 0);
    $selectedTradeDate = old('trade_date', optional($trade?->trade_date)->toDateString() ?? request('trade_date', now()->toDateString()));
    $selectedSymbolId = old('symbol_id', $trade?->symbol_id ?? request('symbol_id'));
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5" x-data="tradeForm({
    hasEntryFees: {{ (float) $entryFees > 0 ? 'true' : 'false' }},
    hasExitFees: {{ (float) $exitFees > 0 ? 'true' : 'false' }},
    existingImageIds: @js($trade?->images?->pluck('id')?->values() ?? []),
    dailyBiases: @js($dailyBiases ?? []),
    enforceDailyBias: @js($enforceDailyBias ?? true),
    selectedTradeDate: @js($selectedTradeDate),
    selectedSymbolId: @js($selectedSymbolId ? (string) $selectedSymbolId : ''),
})">
    @csrf
    @if(! in_array(strtoupper($method), ['GET', 'POST'], true))
        @method($method)
    @endif

    <div class="admin-panel p-5">
        <div class="mb-4">
            <div x-show="enforceDailyBias && selectedDailyBias()" x-cloak>
                <div class="flex flex-col gap-2 rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-950/60 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Daily bias</span>
                        <span class="rounded-md px-2 py-1 text-xs font-semibold capitalize" :class="biasClass(selectedDailyBias()?.bias)" x-text="selectedDailyBias()?.bias"></span>
                        <span class="font-medium text-slate-700 dark:text-slate-200" x-text="selectedDailyBias()?.symbol_name"></span>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-slate-500 dark:text-slate-400">
                        <span x-show="selectedDailyBias()?.htf_trend" x-text="`HTF: ${selectedDailyBias()?.htf_trend}`"></span>
                        <span x-show="selectedDailyBias()?.expected_move" x-text="`Expected: ${selectedDailyBias()?.expected_move}`"></span>
                    </div>
                </div>
            </div>
            <div x-show="enforceDailyBias && selectedTradeDate && !selectedSymbolId && symbolsForSelectedDate().length === 0" x-cloak>
                <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
                    Create a daily bias for this date before punching a trade.
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-input label="Trade Date" name="trade_date" type="date" value="{{ $selectedTradeDate }}" x-model="selectedTradeDate" required />
            <x-select label="Symbol" name="symbol_id" x-model="selectedSymbolId" required>
                <option value="">Select symbol</option>
                @foreach($symbols as $symbol)
                    <option
                        value="{{ $symbol->id }}"
                        x-bind:hidden="enforceDailyBias && !hasDailyBiasForSymbol({{ $symbol->id }})"
                        x-bind:disabled="enforceDailyBias && !hasDailyBiasForSymbol({{ $symbol->id }})"
                        @selected((string) $selectedSymbolId === (string) $symbol->id)
                    >{{ $symbol->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Direction" name="direction" required>
                <option value="long" @selected(old('direction', $trade?->direction ?? 'long') === 'long')>Long</option>
                <option value="short" @selected(old('direction', $trade?->direction) === 'short')>Short</option>
            </x-select>
            <x-select label="Setup" name="setup_type">
                <option value="">Unspecified</option>
                @foreach($setups as $setup)
                    <option value="{{ $setup->name }}" @selected(old('setup_type', $trade?->setup_type) === $setup->name)>{{ $setup->name }}</option>
                @endforeach
            </x-select>
            <x-input label="Entry" name="entry_price" type="number" step="0.0001" value="{{ old('entry_price', $trade?->entry_price) }}" required />
            <x-input label="Stop Loss" name="stop_loss" type="number" step="0.0001" value="{{ old('stop_loss', $trade?->stop_loss) }}" required />
            <x-input label="Target" name="target_price" type="number" step="0.0001" value="{{ old('target_price', $trade?->target_price) }}" />
            <x-input label="Position Size" name="position_size" type="number" step="0.0001" value="{{ old('position_size', $trade?->position_size) }}" required />
            <x-input label="Exit Price" name="exit_price" type="number" step="0.0001" value="{{ old('exit_price', $trade?->exit_price) }}" />

            <div class="space-y-2">
                <input type="hidden" name="entry_fees" value="0" :disabled="hasEntryFees">
                <label class="flex min-h-[2.625rem] items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700" x-model="hasEntryFees">
                    Entry fees
                </label>
                <div x-show="hasEntryFees" x-cloak>
                    <x-input label="Entry Fees Amount" name="entry_fees" type="number" step="0.0001" min="0" value="{{ $entryFees }}" x-bind:disabled="!hasEntryFees" />
                </div>
            </div>

            <div class="space-y-2">
                <input type="hidden" name="exit_fees" value="0" :disabled="hasExitFees">
                <label class="flex min-h-[2.625rem] items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700" x-model="hasExitFees">
                    Exit fees
                </label>
                <div x-show="hasExitFees" x-cloak>
                    <x-input label="Exit Fees Amount" name="exit_fees" type="number" step="0.0001" min="0" value="{{ $exitFees }}" x-bind:disabled="!hasExitFees" />
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1fr_18rem]">
        <label class="admin-panel block p-5">
            <span class="control-label">Notes</span>
            <textarea name="notes" rows="6" class="control-field">{{ old('notes', $trade?->notes) }}</textarea>
        </label>

        <div class="admin-panel space-y-3 p-5">
            <div class="text-sm font-semibold text-slate-950 dark:text-white">Review flags</div>
            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="mistake_flag" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700" @checked(old('mistake_flag', $trade?->mistake_flag))>
                Mistake flag
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="emotion_flag" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700" @checked(old('emotion_flag', $trade?->emotion_flag))>
                Emotion flag
            </label>
        </div>
    </div>

    @if($trade?->images?->isNotEmpty())
        <div class="admin-panel p-5">
            <div class="mb-3 text-sm font-semibold text-slate-950 dark:text-white">Existing chart images</div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($trade->images as $image)
                    <div x-show="hasExistingImage({{ $image->id }})" class="relative rounded-lg border border-slate-200 p-2 transition hover:border-blue-300 dark:border-slate-800 dark:hover:border-blue-800">
                        <input type="hidden" name="delete_image_ids[]" value="{{ $image->id }}" x-bind:disabled="hasExistingImage({{ $image->id }})">
                        <button type="button" @click="removeExistingImage({{ $image->id }})" class="absolute right-3 top-3 z-10 flex h-7 w-7 items-center justify-center rounded-full bg-white/95 text-slate-500 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-600 dark:bg-slate-950/95 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-red-950 dark:hover:text-red-300" aria-label="Delete image {{ $image->timeframe }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <a href="{{ Storage::url($image->image_path) }}" target="_blank" class="block">
                            <img src="{{ Storage::url($image->image_path) }}" alt="{{ $image->timeframe }}" class="h-32 w-full rounded-md object-cover">
                            <span class="mt-2 block text-xs font-medium text-slate-500">{{ $image->timeframe }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="admin-panel border-2 border-dashed border-slate-300 p-5 dark:border-slate-700" @dragover.prevent @drop.prevent="addFiles($event.dataTransfer.files)" @paste.window="paste($event)">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="font-medium text-slate-950 dark:text-white">Chart images</div>
                <div class="text-sm text-slate-500">Paste from clipboard, drag files here, or browse.</div>
            </div>
            <input x-ref="fileInput" type="file" name="images[]" multiple accept="image/*" @change="syncItems()" class="text-sm">
        </div>
        <template x-for="(item, index) in items" :key="index">
            <div class="mt-3 grid gap-2 sm:grid-cols-[160px_1fr_auto]">
                <select :name="`image_timeframe[${index}]`" class="control-field mt-0">
                    @foreach($timeframes as $timeframe)<option value="{{ $timeframe }}">{{ $timeframe }}</option>@endforeach
                </select>
                <div class="self-center text-sm text-slate-500" x-text="item.name"></div>
                <button type="button" @click="removeFile(index)" class="flex h-10 w-10 items-center justify-center rounded-full text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:text-slate-300 dark:hover:bg-red-950 dark:hover:text-red-300" :aria-label="`Remove ${item.name}`">
                    <span class="text-lg leading-none" aria-hidden="true">&times;</span>
                </button>
            </div>
        </template>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ $trade ? route('trades.show', $trade) : route('trades.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
        <button class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700">{{ $submitLabel }}</button>
    </div>
</form>

@once
<script>
function tradeForm({
    hasEntryFees = false,
    hasExitFees = false,
    existingImageIds = [],
    dailyBiases = [],
    enforceDailyBias = true,
    selectedTradeDate = '',
    selectedSymbolId = '',
} = {}) {
    return {
        hasEntryFees,
        hasExitFees,
        existingImageIds: existingImageIds.map(Number),
        dailyBiases: dailyBiases.map(bias => ({
            ...bias,
            symbol_id: String(bias.symbol_id),
        })),
        enforceDailyBias,
        selectedTradeDate,
        selectedSymbolId: selectedSymbolId ? String(selectedSymbolId) : '',
        items: [],
        store: new DataTransfer(),
        init() {
            if (! this.enforceDailyBias) {
                return;
            }

            if (this.selectedSymbolId && ! this.selectedDailyBias()) {
                this.selectedSymbolId = '';
            }

            this.$watch('selectedTradeDate', () => {
                if (this.selectedSymbolId && ! this.selectedDailyBias()) {
                    this.selectedSymbolId = '';
                }
            });
        },
        symbolsForSelectedDate() {
            const symbols = new Map();

            this.dailyBiases
                .filter(bias => bias.date === this.selectedTradeDate)
                .forEach(bias => symbols.set(bias.symbol_id, { id: bias.symbol_id, name: bias.symbol_name }));

            return Array.from(symbols.values()).sort((a, b) => a.name.localeCompare(b.name));
        },
        hasDailyBiasForSymbol(symbolId) {
            return this.dailyBiases.some(bias => bias.date === this.selectedTradeDate && bias.symbol_id === String(symbolId));
        },
        selectedDailyBias() {
            return this.dailyBiases.find(bias => bias.date === this.selectedTradeDate && bias.symbol_id === String(this.selectedSymbolId));
        },
        biasClass(bias) {
            return {
                bullish: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
                bearish: 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300',
                neutral: 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
            }[bias] || 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200';
        },
        hasExistingImage(id) {
            return this.existingImageIds.includes(Number(id));
        },
        removeExistingImage(id) {
            this.existingImageIds = this.existingImageIds.filter(existingId => existingId !== Number(id));
        },
        syncItems() {
            this.store = new DataTransfer();
            Array.from(this.$refs.fileInput.files).forEach(file => this.store.items.add(file));
            this.items = Array.from(this.store.files).map(file => ({ name: file.name }));
        },
        removeFile(index) {
            const files = Array.from(this.store.files);
            files.splice(index, 1);
            this.store = new DataTransfer();
            files.forEach(file => this.store.items.add(file));
            this.$refs.fileInput.files = this.store.files;
            this.items = Array.from(this.store.files).map(file => ({ name: file.name }));
        },
        addFiles(files) {
            Array.from(files).forEach(file => this.store.items.add(file));
            this.$refs.fileInput.files = this.store.files;
            this.items = Array.from(this.store.files).map(file => ({ name: file.name }));
        },
        paste(event) {
            const files = Array.from(event.clipboardData?.files || []).filter(file => file.type.startsWith('image/'));
            if (files.length) this.addFiles(files);
        }
    }
}
</script>
@endonce
