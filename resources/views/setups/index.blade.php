@extends('layouts.app')

@section('title', 'Setups')

@section('content')
<div class="grid gap-5 lg:grid-cols-[360px_1fr]">
    <form method="POST" action="{{ route('setups.store') }}" class="admin-panel space-y-4 p-5">
        @csrf
        <div>
            <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Create setup</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Keep your trade patterns consistent.</p>
        </div>
        <x-input label="Name" name="name" required />
        <label class="block"><span class="control-label">Description</span><textarea name="description" rows="5" class="control-field"></textarea></label>
        <button class="primary-button w-full">Create Setup</button>
    </form>
    <div class="table-shell">
        <table class="data-table"><thead><tr><th class="text-left">Name</th><th class="text-left">Description</th></tr></thead>
        <tbody>@foreach($setups as $setup)<tr><td class="font-medium text-slate-950 dark:text-white">{{ $setup->name }}</td><td>{{ $setup->description ?: '-' }}</td></tr>@endforeach</tbody></table>
        <div class="pagination-wrap">{{ $setups->links() }}</div>
    </div>
</div>
@endsection
