@extends('layouts.app')

@section('title', 'Position Size Calculator')

@section('content')
<div x-data="positionCalculator()" class="space-y-5">
    <div class="page-toolbar border-slate-200/90 bg-white/90 shadow-md shadow-slate-900/5 ring-1 ring-white/70 backdrop-blur dark:border-slate-800/90 dark:bg-slate-900/90 dark:ring-slate-700/20">
        <div>
            <p class="text-sm font-semibold text-slate-950 dark:text-white">Position size calculator</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tune capital, risk, stop distance, and leverage with live sizing feedback.</p>
        </div>
        <div class="flex items-center gap-2 rounded-md border border-slate-200 bg-slate-100/80 p-1 shadow-inner dark:border-slate-800 dark:bg-slate-950/80">
            <button type="button" @click="direction = 'long'" class="rounded px-3 py-1.5 text-xs font-semibold transition" :class="direction === 'long' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white'">Long</button>
            <button type="button" @click="direction = 'short'" class="rounded px-3 py-1.5 text-xs font-semibold transition" :class="direction === 'short' ? 'bg-red-600 text-white shadow-sm shadow-red-600/20' : 'text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white'">Short</button>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(360px,480px)_1fr]">
        <div class="admin-panel overflow-hidden border-slate-200/90 bg-white/95 shadow-xl shadow-slate-900/[0.04] ring-1 ring-white/80 dark:border-slate-800/90 dark:bg-slate-900/95 dark:ring-slate-700/20">
            <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-white to-blue-50/60 px-5 py-4 dark:border-slate-800 dark:from-slate-950/70 dark:via-slate-900 dark:to-blue-950/20">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Trade Inputs</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Change any value and results update instantly.</p>
                    </div>
                    <span class="rounded-md px-2.5 py-1 text-xs font-semibold" :class="direction === 'long' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300'" x-text="direction.toUpperCase()"></span>
                </div>
            </div>

            <div class="space-y-5 p-5">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <span class="control-label">Instrument</span>
                        <div class="mt-2 grid grid-cols-3 rounded-md border border-slate-200 bg-slate-100/80 p-1 shadow-inner dark:border-slate-800 dark:bg-slate-950/80">
                            <template x-for="option in ['crypto', 'equity', 'futures']" :key="option">
                                <button type="button" @click="instrument = option" class="rounded px-2 py-2 text-xs font-semibold capitalize transition" :class="instrument === option ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-800 dark:text-blue-300' : 'text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white'" x-text="option"></button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <span class="control-label">Currency</span>
                        <div class="mt-2 grid grid-cols-2 rounded-md border border-slate-200 bg-slate-100/80 p-1 shadow-inner dark:border-slate-800 dark:bg-slate-950/80">
                            <button type="button" @click="setCurrency('INR')" class="rounded px-3 py-2 text-xs font-semibold transition" :class="currency === 'INR' ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-800 dark:text-blue-300' : 'text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white'">INR</button>
                            <button type="button" @click="setCurrency('USDT')" class="rounded px-3 py-2 text-xs font-semibold transition" :class="currency === 'USDT' ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-800 dark:text-blue-300' : 'text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white'">USDT</button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="control-label">Trading Capital</span>
                        <div class="mt-1 flex rounded-md border border-slate-300/90 bg-white shadow-sm shadow-slate-900/5 transition focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:focus-within:border-blue-400">
                            <span class="grid min-w-14 place-items-center border-r border-slate-200 px-3 text-xs font-semibold text-slate-500 dark:border-slate-800 dark:text-slate-400" x-text="currency"></span>
                            <input name="trading_capital" type="number" step="0.01" x-model.number="capital" @blur="formatCapital()" class="w-full rounded-r-md border-0 bg-transparent px-3 py-2 text-sm text-slate-900 outline-none dark:text-slate-100">
                        </div>
                    </label>

                    <label class="block">
                        <span class="control-label">USDT Conversion Rate</span>
                        <input name="usdt_rate" type="number" step="0.01" x-model.number="usdtRate" class="control-field">
                    </label>
                </div>

                <div class="rounded-lg border border-slate-200/90 bg-slate-50/60 p-4 shadow-sm shadow-slate-900/[0.03] dark:border-slate-800 dark:bg-slate-950/30">
                    <div class="flex items-center justify-between gap-3">
                        <span class="control-label">Risk Method</span>
                        <div class="grid grid-cols-2 rounded-md border border-slate-200 bg-slate-100/80 p-1 shadow-inner dark:border-slate-800 dark:bg-slate-950/80">
                            <button type="button" @click="riskMethod = 'percent'" class="rounded px-3 py-1.5 text-xs font-semibold transition" :class="riskMethod === 'percent' ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-800 dark:text-blue-300' : 'text-slate-600 dark:text-slate-300'">Percent</button>
                            <button type="button" @click="riskMethod = 'fixed'" class="rounded px-3 py-1.5 text-xs font-semibold transition" :class="riskMethod === 'fixed' ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-800 dark:text-blue-300' : 'text-slate-600 dark:text-slate-300'">Fixed</button>
                        </div>
                    </div>

                    <div x-show="riskMethod === 'percent'" x-cloak class="mt-4">
                        <div class="flex items-end justify-between gap-3">
                            <label class="min-w-0 flex-1">
                                <span class="control-label">Risk Percent</span>
                                <input name="risk_percent" type="number" step="0.01" x-model.number="riskPercent" class="control-field">
                            </label>
                            <div class="pb-2 text-right">
                                <div class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-white"><span x-text="money(riskPercent)"></span>%</div>
                            </div>
                        </div>
                        <input type="range" min="0" max="10" step="0.1" x-model.number="riskPercent" class="mt-3 h-2 w-full cursor-pointer accent-blue-600">
                    </div>

                    <div x-show="riskMethod === 'fixed'" x-cloak class="mt-4">
                        <label class="block">
                            <span class="control-label">Fixed Risk Amount (<span x-text="currency"></span>)</span>
                            <input id="fixed_risk_amount" name="fixed_risk_amount" type="number" step="0.01" x-model.number="fixedRisk" class="control-field">
                        </label>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="control-label">Entry Price</span>
                        <input name="entry_price" type="number" step="0.0001" x-model.number="entry" class="control-field">
                    </label>

                    <label class="block">
                        <span class="control-label">Stoploss Price</span>
                        <input name="stoploss_price" type="number" step="0.0001" x-model.number="stop" class="control-field">
                    </label>
                </div>

                <div class="rounded-lg border border-slate-200/90 bg-slate-50/60 p-4 shadow-sm shadow-slate-900/[0.03] dark:border-slate-800 dark:bg-slate-950/30">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="control-label">Stop Distance</span>
                        <span class="text-sm font-semibold text-slate-950 dark:text-white" x-text="money(slDistance)"></span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full border border-slate-200 bg-white shadow-inner dark:border-slate-800 dark:bg-slate-900">
                        <div class="h-full rounded-full transition-all" :class="direction === 'long' ? 'bg-gradient-to-r from-emerald-400 to-emerald-600' : 'bg-gradient-to-r from-red-400 to-red-600'" :style="`width: ${riskBarWidth}%`"></div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="control-label">Leverage</span>
                        <div class="mt-1 flex items-center gap-2">
                            <button type="button" @click="leverage = Math.max(1, Number(leverage || 1) - 1)" class="icon-button h-10 w-10" aria-label="Decrease leverage">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14"/></svg>
                            </button>
                            <input name="leverage" type="number" step="0.01" x-model.number="leverage" class="control-field mt-0 text-center">
                            <button type="button" @click="leverage = Number(leverage || 0) + 1" class="icon-button h-10 w-10" aria-label="Increase leverage">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                            </button>
                        </div>
                        <input type="range" min="1" max="125" step="1" x-model.number="leverage" class="mt-3 h-2 w-full cursor-pointer accent-blue-600">
                    </label>

                    <label class="block">
                        <span class="control-label">Lot Precision</span>
                        <select name="lot_precision" x-model.number="precision" class="control-field">
                            <option value="1">1</option>
                            <option value="0.1">0.1</option>
                            <option value="0.01">0.01</option>
                            <option value="0.001">0.001</option>
                            <option value="0.0001">0.0001</option>
                            <option value="0.00000001">0.00000001</option>
                        </select>
                    </label>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="admin-panel overflow-hidden border-slate-200/90 bg-white/95 shadow-xl shadow-slate-900/[0.04] ring-1 ring-white/80 dark:border-slate-800/90 dark:bg-slate-900/95 dark:ring-slate-700/20">
                <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-white to-blue-50/60 px-5 py-4 dark:border-slate-800 dark:from-slate-950/70 dark:via-slate-900 dark:to-blue-950/20">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Live Position</h2>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Capital required after leverage, shown in INR and USDT.</p>
                        </div>
                        <div class="rounded-md bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                            <span x-text="instrument.toUpperCase()"></span> / <span x-text="currency"></span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 p-5 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <div class="control-label">Position Value</div>
                        <div class="mt-2 break-words text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">
                            <span x-text="currencyMoney(positionValue)"></span>
                            <span class="text-xl text-slate-400" x-text="currencyLabel"></span>
                        </div>
                        <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            <span x-text="alternateMoney(positionValue)"></span>
                            <span class="mx-2 text-slate-300 dark:text-slate-700">/</span>
                            <span x-text="quantityMoney(quantity)"></span> units
                        </div>
                    </div>
                    <div class="rounded-lg border border-blue-100 bg-gradient-to-br from-white to-blue-50/60 p-4 shadow-sm shadow-blue-900/5 dark:border-blue-950/60 dark:from-slate-900 dark:to-blue-950/20">
                        <div class="control-label">Capital Required</div>
                        <div class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">
                            <span x-text="currencyMoney(capitalRequired)"></span>
                            <span class="text-sm text-slate-400" x-text="currencyLabel"></span>
                        </div>
                        <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            <span x-text="alternateMoney(capitalRequired)"></span>
                            <span class="mx-1">with</span>
                            <span x-text="money(leverage)"></span>x leverage
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <template x-for="item in outputs" :key="item.label">
                    <div class="admin-panel relative overflow-hidden border-slate-200/90 bg-white/95 p-5 shadow-md shadow-slate-900/[0.035] ring-1 ring-white/70 transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-xl hover:shadow-slate-900/7 dark:border-slate-800/90 dark:bg-slate-900/95 dark:ring-slate-700/20 dark:hover:border-blue-900/60">
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-cyan-400 to-emerald-400"></div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="control-label" x-text="item.label"></div>
                            <div class="h-2.5 w-2.5 rounded-full bg-blue-500 shadow-sm shadow-blue-500/40"></div>
                        </div>
                        <div class="mt-3 break-words text-2xl font-semibold tracking-tight text-slate-950 dark:text-white" x-text="item.value"></div>
                        <div x-show="item.secondary" x-cloak class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400" x-text="item.secondary"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function positionCalculator() {
    return {
        instrument: 'crypto', currency: 'INR', capital: 100000, usdtRate: 85, riskMethod: 'percent', riskPercent: 1, fixedRisk: 1000, entry: 100, stop: 95, leverage: 1, precision: 0.00000001, direction: 'long',
        roundLot(value) { const p = Number(this.precision) || 1; return Math.floor(value / p) * p; },
        money(value) { return Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 8 }); },
        usdtMoney(value) { return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
        roundTo(value, decimals) { const factor = 10 ** decimals; return Math.round((Number(value) || 0) * factor) / factor; },
        quantityMoney(value) { return Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 4 }); },
        currencyMoney(value) { return this.currency === 'USDT' ? this.usdtMoney(value) : this.money(value); },
        formatCapital() {
            if (this.currency === 'USDT') {
                this.capital = this.roundTo(this.capital, 2);
            }
        },
        setCurrency(nextCurrency) {
            if (this.currency === nextCurrency) {
                return;
            }

            if (this.currency === 'INR' && nextCurrency === 'USDT') {
                this.capital = this.roundTo(this.inrToUsdt(this.capital), 2);
                this.fixedRisk = this.inrToUsdt(this.fixedRisk);
            }

            if (this.currency === 'USDT' && nextCurrency === 'INR') {
                this.capital = this.usdtToInr(this.capital);
                this.fixedRisk = this.usdtToInr(this.fixedRisk);
            }

            this.currency = nextCurrency;
        },
        inrToUsdt(value) { return this.conversionRate > 0 ? (Number(value) || 0) / this.conversionRate : 0; },
        usdtToInr(value) { return (Number(value) || 0) * this.conversionRate; },
        pairedMoney(value) {
            return {
                primary: `${this.currencyMoney(value)} ${this.currencyLabel}`,
                secondary: this.alternateMoney(value),
            };
        },
        alternateMoney(value) {
            return this.currency === 'INR'
                ? `${this.usdtMoney(this.inrToUsdt(value))} USDT`
                : `${this.money(this.usdtToInr(value))} INR`;
        },
        get conversionRate() { return Number(this.usdtRate) || 0; },
        get activeCapital() { return Number(this.capital) || 0; },
        get riskAmount() { return this.riskMethod === 'percent' ? this.activeCapital * (this.riskPercent / 100) : Number(this.fixedRisk || 0); },
        get slDistance() { return Math.abs((Number(this.entry) || 0) - (Number(this.stop) || 0)); },
        get quantity() { return this.slDistance > 0 ? this.roundLot(this.riskAmount / this.slDistance) : 0; },
        get positionSize() { return this.quantity * (Number(this.entry) || 0); },
        get positionValue() { return this.positionSize; },
        get capitalRequired() { return this.leverage > 0 ? this.positionValue / this.leverage : this.positionValue; },
        get currencyLabel() { return this.currency === 'USDT' ? 'USDT' : 'INR'; },
        get riskBarWidth() {
            const entry = Math.abs(Number(this.entry) || 0);
            return entry > 0 ? Math.min(100, (this.slDistance / entry) * 100) : 0;
        },
        get outputs() {
            const output = [
                { label: 'Account', value: this.pairedMoney(this.activeCapital).primary, secondary: this.pairedMoney(this.activeCapital).secondary },
                { label: 'Effective Risk Amount', value: this.pairedMoney(this.riskAmount).primary, secondary: this.pairedMoney(this.riskAmount).secondary },
                { label: 'SL Distance', value: this.money(this.slDistance) },
                { label: 'Position Size', value: this.quantityMoney(this.quantity), secondary: `${this.pairedMoney(this.positionValue).primary} exposure` },
                { label: 'Position Value', value: this.pairedMoney(this.positionValue).primary, secondary: this.pairedMoney(this.positionValue).secondary },
                { label: 'Capital Required', value: this.pairedMoney(this.capitalRequired).primary, secondary: this.pairedMoney(this.capitalRequired).secondary },
            ];

            if (this.currency === 'USDT') {
                output.unshift({ label: 'USDT Conversion Rate', value: `${this.money(this.usdtRate)} INR`, secondary: '1 USDT' });
            }

            return output;
        }
    }
}
</script>
@endsection
