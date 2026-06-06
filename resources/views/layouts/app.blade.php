<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebar: false, profileMenu: false, logoutModal: false, dark: localStorage.getItem('theme') === 'dark' }" x-init="$watch('dark', value => localStorage.setItem('theme', value ? 'dark' : 'light'))" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Trading Journal') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body>
@auth
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 lg:h-screen lg:overflow-hidden">
    <aside class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-200 bg-white/95 shadow-2xl shadow-slate-900/10 backdrop-blur transition duration-300 dark:border-slate-800 dark:bg-slate-950/95 lg:translate-x-0 lg:shadow-none" :class="{ 'translate-x-0': sidebar }">
        <div class="flex h-16 items-center justify-between border-b border-slate-200 px-5 dark:border-slate-800">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-base font-semibold tracking-tight">
                <span class="grid h-9 w-9 place-items-center rounded-md bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-600/20">TJ</span>
                <span>Trading Journal</span>
            </a>
            <button type="button" class="icon-button lg:hidden" @click="sidebar = false" aria-label="Close navigation">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <nav class="space-y-1 p-3">
            @php
                $links = [
                    ['Dashboard', 'dashboard', 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'],
                    ['Trades', 'trades.index', 'M4 7h16M4 12h16M4 17h10'],
                    ['Daily Bias', 'daily-biases.index', 'M12 3v18m9-9H3'],
                    ['Position Size Calculator', 'calculator', 'M6 6h12v12H6zM9 9h6v6H9z'],
                    ['Analytics', 'analytics', 'M4 19V9m6 10V5m6 14v-7m4 7H3'],
                    ['Symbols', 'symbols.index', 'M7 7h10v10H7zM4 4h16v16H4z'],
                    ['Setups', 'setups.index', 'M12 6v12m6-6H6'],
                ];
            @endphp
            @foreach($links as [$label, $route, $icon])
                <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs($route) ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
    </aside>
    <div x-show="sidebar" x-cloak class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="sidebar = false"></div>
    <main class="min-w-0 lg:ml-72 lg:flex lg:h-screen lg:flex-col">
        <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/90 px-4 shadow-sm shadow-slate-900/5 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/90 sm:px-6">
            <div class="flex items-center gap-3">
                <button class="icon-button lg:hidden" @click="sidebar = true" aria-label="Open navigation">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-base font-semibold tracking-tight text-slate-950 dark:text-white">@yield('title', 'Dashboard')</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Performance workspace</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="dark = !dark" class="icon-button" aria-label="Toggle theme">
                    <svg x-show="!dark" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A8.5 8.5 0 1111.2 3a6.5 6.5 0 009.8 9.8z"/></svg>
                    <svg x-show="dark" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.4-6.4L17 7M7 17l-1.4 1.4M18.4 18.4L17 17M7 7 5.6 5.6M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>

                @php
                    $user = auth()->user();
                    $displayName = $user?->name ?: $user?->email;
                    $initial = strtoupper(substr($displayName ?: 'U', 0, 1));
                @endphp
                <div class="relative" @click.outside="profileMenu = false">
                    <button type="button" @click="profileMenu = !profileMenu" class="flex items-center gap-2 rounded-md border border-slate-200 bg-white px-2 py-1.5 text-left shadow-sm shadow-slate-900/5 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700 dark:hover:bg-slate-800" aria-label="Open profile menu">
                        <span class="grid h-8 w-8 place-items-center rounded-md bg-blue-600 text-xs font-bold text-white shadow-sm shadow-blue-600/20">{{ $initial }}</span>
                        <span class="hidden min-w-0 sm:block">
                            <span class="block max-w-36 truncate text-sm font-semibold text-slate-950 dark:text-white">{{ $displayName }}</span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">Account</span>
                        </span>
                        <svg class="h-4 w-4 text-slate-500 transition dark:text-slate-400" :class="{ 'rotate-180': profileMenu }" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                    </button>

                    <div x-show="profileMenu" x-cloak class="absolute right-0 mt-2 w-56 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900" @keydown.escape.window="profileMenu = false">
                        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                            <p class="truncate text-sm font-semibold text-slate-950 dark:text-white">{{ $displayName }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $user?->email }}</p>
                        </div>
                        <div class="p-1.5">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zm7 8a7 7 0 10-14 0"/></svg>
                                Profile
                            </a>
                            <button type="button" @click="profileMenu = false; logoutModal = true" class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-950/50">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m12-7l5 7-5 7"/></svg>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <section class="p-4 sm:p-6 lg:flex-1 lg:overflow-y-auto">
            @if(session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </section>
    </main>

    <div x-show="logoutModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" @keydown.escape.window="logoutModal = false">
        <div class="w-full max-w-sm rounded-lg border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-900" @click.outside="logoutModal = false">
            <div class="flex items-start gap-3">
                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m12-7l5 7-5 7"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-slate-950 dark:text-white">Logout?</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">You will be signed out of your trading journal.</p>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button" @click="logoutModal = false" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-red-600/25 transition hover:bg-red-700">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>
@else
    @yield('content')
@endauth
</body>
</html>
