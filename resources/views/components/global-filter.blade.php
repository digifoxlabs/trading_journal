@props(['symbols', 'setups'])
<form @submit.prevent="refresh" class="admin-panel p-4 sm:p-5">
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div x-data="periodPicker()" x-init="init()" class="relative sm:col-span-2 xl:col-span-1">
            <input type="hidden" name="period" x-model="filters.period">
            <input type="hidden" name="start_date" x-model="filters.start_date">
            <input type="hidden" name="end_date" x-model="filters.end_date">

            <span class="control-label">Period</span>
            <button type="button" @click="open = !open" class="control-field flex items-center justify-between text-left">
                <span x-text="buttonLabel()"></span>
                <svg class="h-4 w-4 text-slate-400 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-125"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                @click.outside="open = false"
                x-cloak
                class="absolute left-0 top-full z-40 mt-2 w-full min-w-[21rem] rounded-lg border border-slate-200 bg-white p-3 shadow-2xl shadow-slate-900/15 dark:border-slate-700 dark:bg-slate-900 sm:min-w-[25rem]"
            >
                <div class="grid grid-cols-2 gap-2">
                    <template x-for="option in options" :key="option.value">
                        <button
                            type="button"
                            @click="selectPeriod(option.value)"
                            class="period-option"
                            :class="{ 'period-option-active': filters.period === option.value }"
                            x-text="option.label"
                        ></button>
                    </template>
                </div>

                <div
                    x-show="filters.period === 'custom'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-cloak
                    class="mt-3 border-t border-slate-200 pt-3 dark:border-slate-800"
                >
                    <div class="mb-3 rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:bg-slate-950 dark:text-slate-300">
                        <span class="font-semibold text-slate-950 dark:text-white" x-text="rangeLabel()"></span>
                    </div>
                    <input x-ref="rangeInput" type="text" class="sr-only" aria-label="Custom date range">
                    <div x-ref="calendar"></div>
                </div>
            </div>
        </div>

        <x-select label="Symbol" name="symbol_id" x-model="filters.symbol_id">
            <option value="">All symbols</option>
            @foreach($symbols as $symbol)<option value="{{ $symbol->id }}">{{ $symbol->name }}</option>@endforeach
        </x-select>
        <x-select label="Setup" name="setup_type" x-model="filters.setup_type">
            <option value="">All setups</option>
            @foreach($setups as $setup)<option value="{{ $setup->name }}">{{ $setup->name }}</option>@endforeach
        </x-select>
        <x-select label="Direction" name="direction" x-model="filters.direction">
            <option value="">All directions</option><option value="long">Long</option><option value="short">Short</option>
        </x-select>
        <x-select label="Mistake" name="mistake_flag" x-model="filters.mistake_flag">
            <option value="">All trades</option><option value="1">Flagged</option><option value="0">Clean</option>
        </x-select>
        <x-select label="Emotion" name="emotion_flag" x-model="filters.emotion_flag">
            <option value="">All states</option><option value="1">Flagged</option><option value="0">Clean</option>
        </x-select>
        <x-select label="Timeframe" name="timeframe" x-model="filters.timeframe">
            <option value="">All timeframes</option><option>1m</option><option>5m</option><option>15m</option><option>1h</option><option>4h</option><option>1D</option><option>1W</option>
        </x-select>
        <button class="self-end rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20">
            Apply filters
        </button>
    </div>
</form>
