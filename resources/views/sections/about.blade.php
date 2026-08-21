<section id="about" class="relative px-6 py-28 lg:px-10 lg:py-40">
    <div class="mx-auto max-w-7xl">

        <div class="grid gap-16 lg:grid-cols-12">

            <div class="lg:col-span-4">
                <p class="eyebrow" data-reveal="fade">01 / About</p>
                <h2 class="mt-6 text-4xl leading-tight font-semibold tracking-tight lg:text-5xl" data-reveal="up">
                    Most of my work<br>is invisible.
                </h2>

                {{-- Availability card --}}
                <div class="panel panel-sheen spotlight mt-10 p-7" data-reveal="up" data-spotlight>
                    <div class="flex items-center gap-3">
                        @if ($profile->available)
                            <span class="animate-pulse-dot h-2 w-2 rounded-full bg-cyan-glow"></span>
                            <h3 class="font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">
                                Available for work
                            </h3>
                        @else
                            <span class="h-2 w-2 rounded-full bg-faint"></span>
                            <h3 class="font-mono text-xs tracking-[0.25em] text-faint uppercase">
                                Not currently available
                            </h3>
                        @endif
                    </div>

                    @if ($profile->availability_modes)
                        <dl class="mt-6 space-y-4">
                            <div>
                                <dt class="font-mono text-[10px] tracking-widest text-faint uppercase">
                                    Work arrangement
                                </dt>
                                <dd class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($profile->availability_modes as $mode)
                                        <span class="chip">{{ $mode }}</span>
                                    @endforeach
                                </dd>
                            </div>

                            <div>
                                <dt class="font-mono text-[10px] tracking-widest text-faint uppercase">
                                    Based in
                                </dt>
                                <dd class="mt-2 text-sm text-bright">{{ $profile->location }}</dd>
                            </div>
                        </dl>
                    @endif

                    @if ($profile->availability_note)
                        <p class="mt-6 border-t border-edge pt-5 text-sm leading-relaxed text-muted">
                            {{ $profile->availability_note }}
                        </p>
                    @endif

                    <div class="mt-6 space-y-2">
                        <a href="mailto:{{ $profile->email }}"
                           class="block font-mono text-xs text-muted transition-colors hover:text-cyan-glow">
                            {{ $profile->email }}
                        </a>

                        @if ($profile->phone)
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $profile->phone) }}"
                               class="block font-mono text-xs text-muted transition-colors hover:text-cyan-glow">
                                {{ $profile->phone }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="space-y-6 text-lg leading-relaxed text-muted" data-reveal="up">
                    <p>
                        I work on the half of the product nobody sees until it breaks. Schemas, queues,
                        caches, the retry logic that quietly saves a payment at two in the morning.
                        When it is done well, users never think about it once.
                    </p>
                    <p>
                        My default is
                        <span class="text-bright">Laravel and PHP</span>, reaching for
                        <span class="text-bright">Go</span> when a service needs to be small and fast,
                        and <span class="text-bright">Postgres</span> for anything where correctness matters
                        more than convenience. I like tracing a slow endpoint down to the exact query,
                        and I like deleting code more than writing it.
                    </p>
                </div>

                {{-- Principles grid --}}
                <div class="mt-14 grid gap-px overflow-hidden rounded-2xl border border-edge bg-edge sm:grid-cols-2">
                    @foreach ($site->principles() as $principle)
                        <article class="spotlight panel-sheen relative bg-ink p-7" data-reveal="up" data-spotlight>
                            <h3 class="flex items-center gap-3 font-mono text-sm tracking-wide text-cyan-glow">
                                <span class="text-faint">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                {{ $principle->title }}
                            </h3>
                            <p class="mt-3 text-sm leading-relaxed text-muted">{{ $principle->body }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
