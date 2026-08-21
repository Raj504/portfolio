<section id="top" class="relative flex min-h-svh items-center overflow-hidden px-6 pt-32 pb-20 lg:px-10">

    {{-- Blueprint grid sits above the WebGL canvas but below the copy. --}}
    <div class="grid-bg pointer-events-none absolute inset-0 -z-[5]" aria-hidden="true"></div>

    <div class="mx-auto w-full max-w-7xl">

        <p class="eyebrow mb-8" data-reveal="fade">
            {{ $profile->location }} &nbsp;/&nbsp; {{ $profile->role }}
        </p>

        {{-- Each line is split into characters by app.js for the stagger-in. --}}
        <h1 class="max-w-5xl text-[clamp(2.6rem,8.5vw,7.5rem)] leading-[0.92] font-semibold tracking-tight">
            <span class="block overflow-hidden">
                <span class="block" data-split-line>I build the</span>
            </span>
            <span class="block overflow-hidden">
                {{-- Animated whole, not per character: background-clip:text does
                     not extend into inline-block children, so splitting this
                     line would erase the gradient. --}}
                <span class="block bg-linear-to-r from-cyan-glow via-bright to-violet-glow bg-clip-text text-transparent"
                      data-split-line="whole">systems that</span>
            </span>
            <span class="block overflow-hidden">
                <span class="block" data-split-line>stay up.</span>
            </span>
        </h1>

        <div class="mt-12 flex max-w-2xl flex-col gap-8" data-reveal="up">
            <p class="text-lg leading-relaxed text-muted">
                {{ $profile->blurb }}
            </p>

            <div class="flex flex-wrap items-center gap-4">
                <a href="#work" class="btn-glow" data-magnetic>
                    View selected work
                    <span aria-hidden="true">&rarr;</span>
                </a>
                <a href="mailto:{{ $profile->email }}"
                   class="font-mono text-sm tracking-wide link-wipe">
                    {{ $profile->email }}
                </a>
            </div>
        </div>

        {{-- Status strip: current, checkable facts rather than vanity metrics. --}}
        @if ($site->statusItems()->isNotEmpty())
            <dl class="mt-20 grid grid-cols-1 gap-px overflow-hidden rounded-2xl border border-edge bg-edge
                       sm:grid-cols-2 lg:grid-cols-4"
                data-reveal="up">
                @foreach ($site->statusItems() as $item)
                    <div class="spotlight relative flex flex-col bg-ink px-6 py-6 transition-colors duration-500"
                         data-spotlight>
                        <dt class="flex items-center gap-2 font-mono text-[10px] tracking-[0.2em] text-faint uppercase">
                            {{-- Only the first item gets the live dot, so it reads
                                 as "right now" rather than four blinking lights. --}}
                            @if ($loop->first)
                                <span class="animate-pulse-dot h-1.5 w-1.5 rounded-full bg-cyan-glow"
                                      aria-hidden="true"></span>
                            @endif
                            {{ $item->label }}
                        </dt>

                        <dd class="mt-2 text-[15px] leading-snug font-medium text-bright">
                            {{ $item->value }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>

    {{-- Scroll hint --}}
    <div class="absolute bottom-8 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-3 lg:flex"
         aria-hidden="true">
        <span class="font-mono text-[10px] tracking-[0.3em] text-faint uppercase">Scroll</span>
        <span class="h-12 w-px bg-linear-to-b from-cyan-glow to-transparent"></span>
    </div>
</section>

{{-- Infinite tech marquee. The list is duplicated so the loop is seamless. --}}
@php
    $marquee = $site->marqueeItems();
@endphp

<div class="relative overflow-hidden border-y border-edge/60 bg-ink/40 py-5" aria-hidden="true">
    <div class="animate-marquee flex w-max gap-12">
        @foreach ([1, 2] as $pass)
            @foreach ($marquee as $item)
                <span class="flex items-center gap-12 font-mono text-sm tracking-[0.2em] text-faint uppercase whitespace-nowrap">
                    {{ $item }}
                    <span class="text-cyan-glow/50">&#9670;</span>
                </span>
            @endforeach
        @endforeach
    </div>
</div>
