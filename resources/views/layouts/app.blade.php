<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @if (! empty($seo))
        <title>{{ $seo['title'] }}</title>
        <meta name="description" content="{{ $seo['description'] }}">
        <meta property="og:title" content="{{ $seo['og_title'] }}">
        <meta property="og:description" content="{{ $seo['og_description'] }}">
        <meta property="og:url" content="{{ $seo['og_url'] }}">
        <meta property="og:type" content="{{ $seo['og_type'] }}">
        <link rel="canonical" href="{{ $seo['canonical'] }}">
    @else
        <title>@yield('title', config('app.name'))</title>
        <meta name="description" content="@yield('meta_description', 'DevRates — remote dev jobs, freelancer calculators, and self-hosted tool alternatives.')">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- ADSENSE_HEAD -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
    @stack('schema')
</head>
<body class="min-h-screen antialiased" style="background: var(--color-bg-primary); color: var(--color-text-secondary);">
    @include('partials.nav')

    <main class="mx-auto max-w-6xl px-4 py-8">
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
