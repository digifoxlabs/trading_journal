@extends('layouts.app')

@section('title', 'Position Size Calculator')

@section('content')
<div x-data="positionCalculator()" class="space-y-4">
    <div class="page-toolbar">
        <div>
            <p class="text-sm font-medium text-slate-950 dark:text-white">Position size calculator</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Calculate quantity, exposure, and required capital from your selected risk method.</p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[420px_1fr]">
    <div class="admin-panel space-y-4 p-5">
        <x-select label="Instrument" name="instrument" x-model="instrument"><option value="crypto">Crypto</option><option value="equity">Equity</option><option value="futures">Futures</option></x-select>
        <x-select label="Currency" name="currency" x-model="currency"><option value="INR">INR</option><option value="USDT">USDT</option></x-select>
        <x-input label="Trading Capital" name="trading_capital" type="number" step="0.01" x-model.number="capital" />
        <div x-show="currency === 'USDT'" x-cloak>
            <x-input label="USDT Conversion Rate" name="usdt_rate" type="number" step="0.01" x-model.number="usdtRate" />
        </div>
        <x-select label="Risk Method" name="risk_method" x-model="riskMethod"><option value="percent">Percent</option><option value="fixed">Fixed</option></x-select>
        <div x-show="riskMethod === 'percent'" x-cloak>
            <x-input label="Risk Percent" name="risk_percent" type="number" step="0.01" x-model.number="riskPercent" />
        </div>
        <div x-show="riskMethod === 'fixed'" x-cloak>
            <label for="fixed_risk_amount" class="block">
                <span class="control-label">Fixed Risk Amount (<span x-text="currency"></span>)</span>
                <input id="fixed_risk_amount" name="fixed_risk_amount" type="number" step="0.01" x-model.number="fixedRisk" class="control-field" />
            </label>
        </div>
        <x-input label="Entry Price" name="entry_price" type="number" step="0.0001" x-model.number="entry" />
        <x-input label="Stoploss Price" name="stoploss_price" type="number" step="0.0001" x-model.number="stop" />
        <x-input label="Leverage" name="leverage" type="number" step="0.01" x-model.number="leverage" />
        <x-select label="Lot Precision" name="lot_precision" x-model.number="precision"><option value="1">1</option><option value="0.1">0.1</option><option value="0.01">0.01</option><option value="0.001">0.001</option><option value="0.0001">0.0001</option><option value="0.00000001">0.00000001</option></x-select>
        <x-select label="Direction" name="direction" x-model="direction"><option value="long">Long</option><option value="short">Short</option></x-select>
    </div>
    <div class="grid content-start gap-4 sm:grid-cols-2">
        <template x-for="item in outputs" :key="item.label">
            <div class="admin-panel p-5">
                <div class="control-label" x-text="item.label"></div>
                <div class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white" x-text="item.value"></div>
            </div>
        </template>
    </div>
    </div>
</div>
<script>
function positionCalculator() {
    return {
        instrument: 'crypto', currency: 'INR', capital: 100000, usdtRate: 85, riskMethod: 'percent', riskPercent: 1, fixedRisk: 1000, entry: 100, stop: 95, leverage: 1, precision: 0.00000001, direction: 'long',
        roundLot(value) { const p = Number(this.precision) || 1; return Math.floor(value / p) * p; },
        money(value) { return Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 8 }); },
        get activeCapital() {
            const capital = Number(this.capital) || 0;
            const rate = Number(this.usdtRate) || 0;

            if (this.currency === 'USDT') {
                return rate > 0 ? capital / rate : 0;
            }

            return capital;
        },
        get riskAmount() { return this.riskMethod === 'percent' ? this.activeCapital * (this.riskPercent / 100) : Number(this.fixedRisk || 0); },
        get slDistance() { return Math.abs((Number(this.entry) || 0) - (Number(this.stop) || 0)); },
        get quantity() { return this.slDistance > 0 ? this.roundLot(this.riskAmount / this.slDistance) : 0; },
        get positionSize() { return this.quantity * (Number(this.entry) || 0); },
        get positionValue() { return this.currency === 'USDT' ? this.quantity * (Number(this.usdtRate) || 0) : this.positionSize; },
        get capitalRequired() { return this.leverage > 0 ? this.positionValue / this.leverage : this.positionValue; },
        get currencyLabel() { return this.currency === 'USDT' ? 'USDT' : 'INR'; },
        get outputs() {
            const output = [
                { label: `Account (${this.currencyLabel})`, value: this.money(this.activeCapital) },
                { label: `Effective Risk Amount (${this.currencyLabel})`, value: this.money(this.riskAmount) },
                { label: 'SL Distance', value: this.money(this.slDistance) },
                { label: 'Position', value: this.money(this.quantity) },
                { label: `Position Value (${this.currency === 'USDT' ? 'INR' : this.currencyLabel})`, value: this.money(this.positionValue) },
                { label: `Capital Required (${this.currency === 'USDT' ? 'INR' : this.currencyLabel})`, value: this.money(this.capitalRequired) },
            ];

            if (this.currency === 'USDT') {
                output.unshift({ label: 'USDT Conversion Rate', value: this.money(this.usdtRate) });
            }

            return output;
        }
    }
}
</script>
@endsection
