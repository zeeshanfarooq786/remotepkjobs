<!DOCTYPE html>
<html lang="en" class="admin-shell">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased admin-shell">
    @if (session('admin_authenticated'))
        <header class="admin-header">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <div class="flex items-center gap-6">
                    <a href="{{ route('admin.blog.index') }}" class="admin-title text-lg font-bold">Admin</a>
                    <nav class="flex gap-4 text-sm">
                        <a href="{{ route('admin.blog.index') }}" class="link-brand font-medium">Blog posts</a>
                        <a href="{{ route('blog.index') }}" class="admin-link-muted hover:underline" target="_blank">View site</a>
                    </nav>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-link-muted text-sm font-medium hover:underline">Log out</button>
                </form>
            </div>
        </header>
    @endif

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('status'))
            <div class="admin-alert-success mb-6 rounded-lg px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="admin-alert-error mb-6 rounded-lg px-4 py-3 text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
