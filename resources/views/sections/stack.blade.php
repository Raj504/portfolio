{{-- overflow-hidden matters: the decorative bloom below is 36rem wide and
     would otherwise stick out past a phone-width viewport, widening the layout
     viewport and pushing the fixed nav's menu button off screen. --}}
<section id="stack" class="relative overflow-hidden px-6 py-28 lg:px-10 lg:py-40">

    {{-- Ambient bloom behind the cards --}}
    <div class="pointer-events-none absolute top-1/2 left-1/2 -z-[5] h-[36rem] w-[36rem]
                -translate-x-1/2 -translate-y-1/2 rounded-full bg-violet-glow/10 blur-[140px]"
         aria-hidden="true"></div>

    <div class="mx-auto max-w-7xl">

        <p class="eyebrow" data-reveal="fade">03 / Stack</p>
        <h2 class="mt-6 max-w-2xl text-4xl leading-tight font-semibold tracking-tight lg:text-5xl" data-reveal="up">
            Tools I reach for<br>without thinking.
        </h2>

        <div class="mt-16 grid gap-6 md:grid-cols-2">
            @foreach ($site->skillGroups() as $group)
                <div class="panel panel-sheen spotlight p-8" data-reveal="up" data-spotlight>
                    <h3 class="font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">
                        {{ $group->name }}
                    </h3>

                    <ul class="mt-6 flex flex-wrap gap-2">
                        @foreach ($group->skills as $item)
                            <li class="chip cursor-default">{{ $item->name }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>
