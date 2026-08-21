@php
    $links = [
        'about' => 'About',
        'work' => 'Work',
        'stack' => 'Stack',
        'path' => 'Path',
        'contact' => 'Contact',
    ];
@endphp

<header id="site-nav"
        class="fixed top-0 z-40 w-full border-b border-transparent transition-all duration-500">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-10">

        {{-- Monogram --}}
        <a href="#top" class="group flex items-center gap-3" aria-label="Back to top">
            <span class="relative flex h-9 w-9 items-center justify-center rounded-lg border border-edge bg-ink
                         font-mono text-sm font-semibold text-cyan-glow transition-colors duration-500
                         group-hover:border-cyan-glow/50">
                {{ collect(explode(' ', $profile->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}
            </span>
            <span class="hidden font-mono text-xs tracking-[0.25em] text-faint uppercase sm:block">
                {{ $profile->role }}
            </span>
        </a>

        {{-- Desktop links --}}
        <div class="hidden items-center gap-8 md:flex">
            @foreach ($links as $anchor => $label)
                <a href="#{{ $anchor }}"
                   data-nav-link="{{ $anchor }}"
                   class="link-wipe font-mono text-xs tracking-widest uppercase">
                    {{ $label }}
                </a>
            @endforeach

            @if ($profile->available)
                <span class="flex items-center gap-2 rounded-full border border-cyan-glow/25 bg-cyan-glow/5 px-3 py-1.5">
                    <span class="animate-pulse-dot h-1.5 w-1.5 rounded-full bg-cyan-glow"></span>
                    <span class="font-mono text-[10px] tracking-widest text-cyan-glow uppercase">Open to work</span>
                </span>
            @endif
        </div>

        {{-- Mobile toggle --}}
        <button id="menu-toggle" type="button"
                class="flex h-9 w-9 items-center justify-center rounded-lg border border-edge md:hidden"
                aria-expanded="false" aria-controls="mobile-menu" aria-label="Open menu">
            <span class="relative block h-3 w-4">
                <span class="menu-bar absolute top-0 left-0 h-px w-full bg-bright transition-transform duration-300"></span>
                <span class="menu-bar absolute bottom-0 left-0 h-px w-full bg-bright transition-transform duration-300"></span>
            </span>
        </button>
    </nav>

    {{-- Mobile drawer --}}
    <div id="mobile-menu"
         class="pointer-events-none max-h-0 overflow-hidden border-t border-edge/0 bg-void/95
                backdrop-blur-xl transition-all duration-500 md:hidden">
        <div class="flex flex-col gap-1 px-6 py-6">
            @foreach ($links as $anchor => $label)
                <a href="#{{ $anchor }}"
                   class="border-b border-edge/60 py-4 font-mono text-sm tracking-widest text-muted uppercase
                          transition-colors hover:text-cyan-glow">
                    <span class="mr-3 text-faint">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $label }}
                </a>
            @endforeach
        </div>
    </div>
</header>
