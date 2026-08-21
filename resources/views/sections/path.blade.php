<section id="path" class="relative px-6 py-28 lg:px-10 lg:py-40">
    <div class="mx-auto max-w-5xl">

        <p class="eyebrow" data-reveal="fade">04 / Path</p>
        <h2 class="mt-6 text-4xl leading-tight font-semibold tracking-tight lg:text-5xl" data-reveal="up">
            Where I have been.
        </h2>

        <div class="relative mt-16 pl-8 sm:pl-12">

            {{-- Rail. The inner element is scaled by GSAP as the section scrolls. --}}
            <div class="absolute top-0 left-0 h-full w-px bg-edge" aria-hidden="true">
                <div id="timeline-fill" class="timeline-rail h-full w-full origin-top scale-y-0"></div>
            </div>

            <div class="space-y-14">
                @foreach ($site->experiences() as $job)
                    <article class="relative" data-reveal="up">

                        {{-- Node on the rail --}}
                        <span class="absolute top-2 -left-8 h-3 w-3 rounded-full border-2 border-cyan-glow
                                     bg-void sm:-left-12" aria-hidden="true"></span>

                        <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                            <h3 class="text-xl font-semibold tracking-tight text-bright">{{ $job->role }}</h3>
                            <span class="font-mono text-xs tracking-widest text-faint uppercase">{{ $job->period }}</span>
                        </div>

                        <p class="mt-1 font-mono text-sm text-cyan-glow">{{ $job->company }}</p>

                        <ul class="mt-5 space-y-3">
                            @foreach ($job->points ?? [] as $point)
                                <li class="flex gap-3 leading-relaxed text-muted">
                                    <span class="mt-2.5 h-px w-4 shrink-0 bg-faint" aria-hidden="true"></span>
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
