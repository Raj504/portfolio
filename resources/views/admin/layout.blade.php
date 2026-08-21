@php
    $nav = [
        'admin.dashboard' => 'Dashboard',
        'admin.analytics' => 'Analytics',
        'admin.profile.edit' => 'Profile',
        'admin.projects.index' => 'Projects',
        'admin.experiences.index' => 'Experience',
        'admin.skills.index' => 'Skills',
        'admin.principles.index' => 'Principles',
        'admin.status.index' => 'Status strip',
        'admin.socials.index' => 'Links',
        'admin.messages.index' => 'Messages',
    ];

    $unread = \App\Models\ContactMessage::unread()->count();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#05060a">
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
{{-- The admin is a working tool: no WebGL, no smooth scroll, just the palette. --}}
<body class="min-h-svh bg-void text-bright antialiased">

<div class="flex min-h-svh flex-col lg:flex-row">

    {{-- Sidebar --}}
    <aside class="shrink-0 border-b border-edge bg-ink lg:w-64 lg:border-r lg:border-b-0">
        <div class="flex items-center justify-between p-6">
            <a href="{{ route('admin.dashboard') }}" class="font-mono text-sm tracking-[0.2em] text-cyan-glow uppercase">
                Admin
            </a>
            <a href="{{ route('home') }}" target="_blank" rel="noopener"
               class="font-mono text-[10px] tracking-widest text-faint uppercase hover:text-cyan-glow">
                View site &nearr;
            </a>
        </div>

        <nav class="flex flex-wrap gap-1 px-3 pb-4 lg:flex-col lg:flex-nowrap">
            @foreach ($nav as $route => $label)
                @php $active = request()->routeIs(Str::before($route, '.index') . '*'); @endphp
                <a href="{{ route($route) }}"
                   @class([
                       'flex items-center justify-between rounded-lg px-3 py-2 font-mono text-xs tracking-wider uppercase transition-colors',
                       'bg-cyan-glow/10 text-cyan-glow' => $active,
                       'text-muted hover:bg-slate-panel hover:text-bright' => ! $active,
                   ])>
                    {{ $label }}
                    @if ($route === 'admin.messages.index' && $unread > 0)
                        <span class="ml-2 rounded-full bg-cyan-glow px-2 py-0.5 text-[10px] font-semibold text-void">
                            {{ $unread }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.logout') }}" class="px-3 pb-6">
            @csrf
            <button type="submit"
                    class="w-full rounded-lg px-3 py-2 text-left font-mono text-xs tracking-wider text-faint
                           uppercase transition-colors hover:bg-slate-panel hover:text-red-400">
                Log out
            </button>
        </form>
    </aside>

    {{-- Content --}}
    <main class="flex-1 p-6 lg:p-10">
        <div class="mx-auto max-w-5xl">

            <header class="flex flex-wrap items-center justify-between gap-4 pb-8">
                <h1 class="text-3xl font-semibold tracking-tight">@yield('heading', 'Admin')</h1>
                @yield('actions')
            </header>

            @if (session('status'))
                <div class="mb-8 rounded-xl border border-cyan-glow/30 bg-cyan-glow/10 px-5 py-4 text-sm text-cyan-glow">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-8 rounded-xl border border-red-500/40 bg-red-500/10 px-5 py-4">
                    <p class="font-mono text-xs tracking-widest text-red-400 uppercase">Could not save</p>
                    <ul class="mt-2 space-y-1 text-sm text-red-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

</body>
</html>
