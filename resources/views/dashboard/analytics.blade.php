@extends('layouts.app')

@section('title', request()->routeIs('analytics') ? 'Analytics' : 'Dashboard')

@section('content')
<div x-data="dashboard(@js($payload))" x-init="draw()" class="space-y-6">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Live performance overview</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">Trading command center</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Filter, compare, and review your journal metrics in one place.</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
            <span class="font-medium text-slate-950 dark:text-white" x-text="payload.metrics.total_trades ?? 0"></span>
            trades in view
        </div>
    </div>

    <x-global-filter :symbols="$symbols" :setups="$setups" />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <template x-for="card in cards" :key="card.key">
            <div class="admin-panel group overflow-hidden p-4 transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg hover:shadow-slate-900/5 dark:hover:border-blue-900/60">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400" x-text="card.label"></div>
                        <div class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white" x-text="format(card.value)"></div>
                    </div>
                    <div class="grid h-9 w-9 place-items-center rounded-md transition" :class="card.tone">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="card.icon"/></svg>
                    </div>
                </div>
                <div class="mt-4 h-1 rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-1 rounded-full transition-all duration-500" :class="card.bar" :style="`width: ${card.width}%`"></div>
                </div>
            </div>
        </template>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div class="admin-panel p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Equity curve</h3>
            <canvas id="equityChart" height="140"></canvas>
        </div>
        <div class="admin-panel p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Monthly PnL</h3>
            <canvas id="monthlyChart" height="140"></canvas>
        </div>
        <div class="admin-panel p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Win ratio</h3>
            <canvas id="winChart" height="140"></canvas>
        </div>
        <div class="admin-panel p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Setup quality</h3>
            <canvas id="setupChart" height="140"></canvas>
        </div>
        <div class="admin-panel p-4 xl:col-span-2">
            <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Symbol performance</h3>
            <canvas id="symbolChart" height="110"></canvas>
        </div>
    </div>
</div>

<script>
function dashboard(initial) {
    return {
        payload: initial,
        filters: { period: 'this_month', start_date: '', end_date: '', symbol_id: '', setup_type: '', direction: '', mistake_flag: '', emotion_flag: '', timeframe: '' },
        charts: {},
        get cards() {
            const m = this.payload.metrics;
            return [
                ['total_trades','Total Trades', 'M4 7h16M4 12h16M4 17h10', 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-200', 'bg-slate-500'],
                ['winning_trades','Winning Trades', 'M5 13l4 4L19 7', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300', 'bg-emerald-500'],
                ['losing_trades','Losing Trades', 'M6 18L18 6M6 6l12 12', 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300', 'bg-rose-500'],
                ['win_rate','Win Rate %', 'M4 19V9m6 10V5m6 14v-7m4 7H3', 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300', 'bg-blue-500'],
                ['loss_rate','Loss Rate %', 'M12 9v4m0 4h.01M10.3 3.9 2-1a2 2 0 011.8 0l2 1a2 2 0 011 1.7v2.2a2 2 0 00.6 1.4l1.7 1.4a2 2 0 01.4 1.9l-.8 2.1a2 2 0 01-1.4 1.2l-2.1.5a2 2 0 00-1.2.9l-1.1 1.9a2 2 0 01-1.8 1h-2.2a2 2 0 01-1.8-1l-1.1-1.9a2 2 0 00-1.2-.9l-2.1-.5A2 2 0 012 16.6l-.8-2.1a2 2 0 01.4-1.9l1.7-1.4A2 2 0 004 9.8V7.6a2 2 0 011-1.7l2-1z', 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300', 'bg-amber-500'],
                ['profit_factor','Profit Factor', 'M12 3v18m6-12a4 4 0 00-4-4h-4a4 4 0 000 8h4a4 4 0 010 8H6', 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300', 'bg-indigo-500'],
                ['expectancy','Expectancy', 'M12 6v6l4 2', 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300', 'bg-cyan-500'],
                ['average_r','Average R', 'M4 17l6-6 4 4 6-8', 'bg-violet-50 text-violet-700 dark:bg-violet-950 dark:text-violet-300', 'bg-violet-500'],
                ['largest_win','Largest Win', 'M7 17L17 7M8 7h9v9', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300', 'bg-emerald-500'],
                ['largest_loss','Largest Loss', 'M17 17L7 7m10 0H8v9', 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300', 'bg-rose-500'],
                ['max_drawdown','Max Drawdown', 'M4 7l6 6 4-4 6 8', 'bg-orange-50 text-orange-700 dark:bg-orange-950 dark:text-orange-300', 'bg-orange-500'],
                ['net_pnl','Net PnL', 'M12 3v18m-5-4h8a3 3 0 000-6H9a3 3 0 010-6h8', 'bg-teal-50 text-teal-700 dark:bg-teal-950 dark:text-teal-300', 'bg-teal-500'],
                ['max_consecutive_wins','Max Consecutive Wins', 'M5 12l4 4L19 6', 'bg-lime-50 text-lime-700 dark:bg-lime-950 dark:text-lime-300', 'bg-lime-500'],
                ['max_consecutive_losses','Max Consecutive Losses', 'M6 6l12 12', 'bg-pink-50 text-pink-700 dark:bg-pink-950 dark:text-pink-300', 'bg-pink-500']
            ].map(([key,label,icon,tone,bar]) => ({ key, label, value: m[key] ?? 0, icon, tone, bar, width: this.cardWidth(key, m[key] ?? 0) }));
        },
        format(value) { return Number.isFinite(Number(value)) ? Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 }) : value; },
        cardWidth(key, value) {
            const number = Math.abs(Number(value) || 0);
            if (key.includes('rate')) return Math.min(100, number);
            return Math.max(12, Math.min(100, number * 8));
        },
        async refresh() {
            const url = new URL('{{ route('dashboard.data') }}');
            Object.entries(this.filters).forEach(([key, value]) => { if (value !== '') url.searchParams.set(key, value); });
            this.payload = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }}).then(r => r.json());
            this.draw();
        },
        draw() {
            Object.values(this.charts).forEach(chart => chart.destroy());
            const c = this.payload.charts;
            const grid = { color: 'rgba(148, 163, 184, 0.18)' };
            const ticks = { color: 'rgb(100, 116, 139)', font: { size: 11 } };
            const options = { responsive: true, plugins: { legend: { labels: { color: 'rgb(100, 116, 139)', boxWidth: 10, usePointStyle: true } } }, scales: { x: { grid, ticks }, y: { grid, ticks } } };
            this.charts.equity = new Chart(document.getElementById('equityChart'), { type: 'line', data: { labels: c.equity.map(p => p.x), datasets: [{ label: 'Equity Curve', data: c.equity.map(p => p.y), borderColor: '#2563eb', backgroundColor: 'rgba(37, 99, 235, .12)', fill: true, pointRadius: 2, tension: .35 }] }, options });
            this.charts.monthly = new Chart(document.getElementById('monthlyChart'), { type: 'bar', data: { labels: c.monthly.labels, datasets: [{ label: 'Monthly PnL', data: c.monthly.values, backgroundColor: '#0f766e', borderRadius: 6 }] }, options });
            this.charts.win = new Chart(document.getElementById('winChart'), { type: 'doughnut', data: { labels: c.win_ratio.labels, datasets: [{ data: c.win_ratio.values, backgroundColor: ['#16a34a','#e11d48','#94a3b8'], borderWidth: 0 }] }, options: { responsive: true, cutout: '64%', plugins: options.plugins } });
            this.charts.setup = new Chart(document.getElementById('setupChart'), { type: 'bar', data: { labels: c.setup.labels, datasets: [{ label: 'Wins', data: c.setup.wins, backgroundColor: '#16a34a', borderRadius: 6 }, { label: 'Losses', data: c.setup.losses, backgroundColor: '#e11d48', borderRadius: 6 }] }, options });
            this.charts.symbol = new Chart(document.getElementById('symbolChart'), { type: 'bar', data: { labels: c.symbols.labels, datasets: [{ label: 'Symbol Performance', data: c.symbols.values, backgroundColor: '#4f46e5', borderRadius: 6 }] }, options: { ...options, indexAxis: 'y' } });
        }
    }
}

function periodPicker() {
    return {
        open: false,
        picker: null,
        options: [
            { value: 'today', label: 'Today' },
            { value: 'last_7_days', label: 'Last 7 days' },
            { value: 'this_month', label: 'This month' },
            { value: 'last_month', label: 'Last month' },
            { value: 'last_quarter', label: 'Last quarter' },
            { value: 'current_financial_year', label: 'Current FY' },
            { value: 'last_financial_year', label: 'Last FY' },
            { value: 'custom', label: 'Custom range' },
        ],
        init() {
            this.$watch('filters.period', value => {
                if (value !== 'custom') {
                    this.filters.start_date = '';
                    this.filters.end_date = '';
                }

                if (value === 'custom') {
                    this.$nextTick(() => this.ensurePicker());
                }
            });

            if (this.filters.period === 'custom') {
                this.$nextTick(() => this.ensurePicker());
            }
        },
        selectPeriod(value) {
            this.filters.period = value;

            if (value === 'custom') {
                this.$nextTick(() => this.ensurePicker());
                return;
            }

            this.open = false;
        },
        ensurePicker() {
            if (this.picker || !window.flatpickr) return;

            this.picker = window.flatpickr(this.$refs.rangeInput, {
                mode: 'range',
                inline: true,
                appendTo: this.$refs.calendar,
                dateFormat: 'Y-m-d',
                defaultDate: [this.filters.start_date, this.filters.end_date].filter(Boolean),
                onReady: (_, __, instance) => {
                    instance.calendarContainer.classList.add('dashboard-calendar');
                },
                onChange: selectedDates => {
                    this.filters.start_date = selectedDates[0] ? this.toValue(selectedDates[0]) : '';
                    this.filters.end_date = selectedDates[1] ? this.toValue(selectedDates[1]) : '';
                },
            });
        },
        buttonLabel() {
            if (this.filters.period === 'custom') {
                return this.rangeLabel();
            }

            return this.options.find(option => option.value === this.filters.period)?.label ?? 'This month';
        },
        rangeLabel() {
            if (!this.filters.start_date && !this.filters.end_date) return 'Choose custom range';
            if (this.filters.start_date && !this.filters.end_date) return `${this.formatDate(this.filters.start_date)} - Select end`;
            return `${this.formatDate(this.filters.start_date)} - ${this.formatDate(this.filters.end_date)}`;
        },
        toValue(date) {
            const month = `${date.getMonth() + 1}`.padStart(2, '0');
            const day = `${date.getDate()}`.padStart(2, '0');
            return `${date.getFullYear()}-${month}-${day}`;
        },
        formatDate(value) {
            const [year, month, day] = value.split('-').map(Number);
            return new Date(year, month - 1, day).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        },
    }
}
</script>
@endsection
