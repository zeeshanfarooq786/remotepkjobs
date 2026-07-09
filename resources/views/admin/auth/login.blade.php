@extends('layouts.admin')

@section('title', 'Login')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="admin-card p-8 shadow-lg">
            <h1 class="admin-heading text-2xl font-bold">Admin login</h1>
            <p class="admin-hint mt-2 text-sm">Manage blog posts manually.</p>

            <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="password" class="admin-label mb-1 block text-sm font-medium">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autofocus
                        class="admin-input text-sm"
                    >
                </div>
                <button type="submit" class="btn-primary w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-white">
                    Sign in
                </button>
            </form>
        </div>
    </div>
@endsection
