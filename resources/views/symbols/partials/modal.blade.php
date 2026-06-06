<div
    x-show="modal === @js($modalId)"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4"
    @keydown.escape.window="modal = null"
>
    <div
        class="w-full max-w-md rounded-lg border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
        @click.outside="modal = null"
    >
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-5 dark:border-slate-800">
            <div>
                <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $title }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
            </div>
            <button type="button" class="icon-button shrink-0" @click="modal = null" aria-label="Close symbol form">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div class="p-5">
            @include('symbols.partials.form', [
                'action' => $action,
                'method' => $method,
                'symbol' => $symbol,
                'submitLabel' => $submitLabel,
                'modalId' => $modalId,
            ])
        </div>
    </div>
</div>
