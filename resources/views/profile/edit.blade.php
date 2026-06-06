@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="grid gap-5 lg:grid-cols-2">
    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        @csrf @method('PUT')
        <h2 class="font-semibold">Account</h2>
        <x-input label="Name" name="name" value="{{ auth()->user()->name }}" required />
        <x-input label="Email" name="email" type="email" value="{{ auth()->user()->email }}" required />
        <button class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Update Profile</button>
    </form>
    <form method="POST" action="{{ route('profile.password') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        @csrf @method('PUT')
        <h2 class="font-semibold">Password</h2>
        <x-input label="Current Password" name="current_password" type="password" required />
        <x-input label="New Password" name="password" type="password" required />
        <x-input label="Confirm Password" name="password_confirmation" type="password" required />
        <button class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Update Password</button>
    </form>
</div>
@endsection
