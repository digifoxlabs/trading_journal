@extends('layouts.app')

@section('content')
<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-4 py-10">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.35),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(20,184,166,0.22),_transparent_34%)]"></div>
    <div class="absolute inset-0 bg-[linear-gradient(135deg,_rgba(255,255,255,0.08)_0,_rgba(255,255,255,0.02)_45%,_rgba(255,255,255,0.06)_100%)]"></div>

    <div class="relative grid w-full max-w-5xl overflow-hidden rounded-2xl border border-white/15 bg-white/10 shadow-2xl shadow-slate-950/50 backdrop-blur-2xl lg:grid-cols-[1fr_28rem]">
        <div class="hidden min-h-[34rem] flex-col justify-between border-r border-white/10 p-8 text-white lg:flex">
            <div>
                <div class="grid h-12 w-12 place-items-center rounded-lg bg-white/15 text-sm font-bold shadow-lg shadow-blue-950/30 ring-1 ring-white/20">TJ</div>
                <h1 class="mt-8 max-w-md text-4xl font-semibold leading-tight">Trading Journal</h1>
                <p class="mt-4 max-w-md text-sm leading-6 text-slate-300">Track your trades, daily bias, execution quality, and performance from one focused workspace.</p>
            </div>

            <div class="grid grid-cols-3 gap-3 text-sm">
                <div class="rounded-lg border border-white/10 bg-white/10 p-3 backdrop-blur">
                    <div class="text-slate-400">Bias</div>
                    <div class="mt-1 font-semibold">Planned</div>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/10 p-3 backdrop-blur">
                    <div class="text-slate-400">Trades</div>
                    <div class="mt-1 font-semibold">Logged</div>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/10 p-3 backdrop-blur">
                    <div class="text-slate-400">Review</div>
                    <div class="mt-1 font-semibold">Clear</div>
                </div>
            </div>
        </div>

        <div class="bg-white/80 p-6 backdrop-blur-xl dark:bg-slate-950/70 sm:p-8">
            <div class="mx-auto max-w-sm">
                <div class="lg:hidden">
                    <div class="grid h-11 w-11 place-items-center rounded-lg bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-600/20">TJ</div>
                </div>

                <div class="mt-8 lg:mt-0">
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Welcome back</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">Sign in to continue</h2>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Access your trading workspace.</p>
                </div>

                @if($errors->any())
                    <div class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-200">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <label class="block">
                        <span class="control-label">Email</span>
                        <input name="email" type="email" value="{{ old('email') }}" required autofocus class="control-field">
                    </label>
                    <label class="block">
                        <span class="control-label">Password</span>
                        <input name="password" type="password" required class="control-field">
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700">
                        Remember me
                    </label>
                    <button class="w-full rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-700">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
