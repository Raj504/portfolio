@php
    $profile = $site->profile();
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#05060a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    {{-- Signed in as the owner: the tracker never boots, so browsing your own
         site costs no beacons and produces no data. --}}
    @auth
        <meta name="analytics" content="off">
    @endauth

    <title>@yield('title', $profile->name . ' — ' . $profile->role)</title>
    <meta name="description" content="{{ $profile->blurb }}">

    {{-- Social preview --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $profile->name }} — {{ $profile->role }}">
    <meta property="og:description" content="{{ $profile->tagline }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet">

    {{-- Set the .js flag before paint so reveal targets hide without a flash. --}}
    <script>document.documentElement.classList.add('js');</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">

    <a href="#main"
       class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100]
              focus:rounded-full focus:bg-cyan-glow focus:px-5 focus:py-2 focus:text-void">
        Skip to content
    </a>

    {{-- WebGL hero canvas. Fixed behind everything; app.js owns it. --}}
    <canvas id="bg-canvas" class="pointer-events-none fixed inset-0 -z-10 h-full w-full"></canvas>

    {{-- Scroll progress bar --}}
    <div class="fixed top-0 left-0 z-50 h-px w-full bg-edge/50">
        <div id="scroll-progress"
             class="h-full origin-left scale-x-0 bg-linear-to-r from-cyan-glow to-violet-glow"></div>
    </div>

    @include('partials.nav')

    <main id="main">
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- Custom cursor: two elements, an inner dot and a lagging ring. --}}
    <div id="cursor-dot" class="pointer-events-none fixed z-[60] hidden h-1.5 w-1.5 rounded-full bg-cyan-glow md:block"></div>
    <div id="cursor-ring" class="pointer-events-none fixed z-[60] hidden h-9 w-9 rounded-full border border-cyan-glow/50 md:block"></div>

</body>
</html>
