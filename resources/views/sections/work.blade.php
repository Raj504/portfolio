@php
    $projects = $site->projects();

    // Featured projects head the section as wide cards. If nothing is flagged,
    // the first one is promoted so the grid always has a focal point.
    $featured = $projects->where('featured', true)->values();

    if ($featured->isEmpty() && $projects->count() > 2) {
        $featured = $projects->take(1)->values();
    }

    $featuredIds = $featured->pluck('id')->all();
    $rest = $projects->whereNotIn('id', $featuredIds)->values();

    // Anything past this stays collapsed behind a button so a long list never
    // turns into an endless scroll of identical cards.
    $visibleCount = 6;
    $hasMore = $rest->count() > $visibleCount;

    // Cycling the accent per card breaks up the uniformity of a big grid.
    // Written as full class strings because Tailwind scans for literals.
    $accents = [
        ['text' => 'group-hover:text-cyan-glow',   'chip' => 'border-cyan-glow/40 text-cyan-glow'],
        ['text' => 'group-hover:text-violet-glow', 'chip' => 'border-violet-glow/40 text-violet-glow'],
        ['text' => 'group-hover:text-amber-glow',  'chip' => 'border-amber-glow/40 text-amber-glow'],
    ];
@endphp

<section id="work" class="relative px-6 py-28 lg:px-10 lg:py-40">
    <div class="mx-auto max-w-7xl">

        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
            <div>
                <p class="eyebrow" data-reveal="fade">02 / Selected work</p>
                <h2 class="mt-6 text-4xl leading-tight font-semibold tracking-tight lg:text-5xl" data-reveal="up">
                    Things I shipped<br>and still maintain.
                </h2>
            </div>

            <div class="max-w-xs" data-reveal="fade">
                <p class="font-mono text-xs leading-relaxed tracking-wide text-faint">
                    Built and maintained end to end, from schema to deploy.
                </p>
                @if ($projects->count() > 3)
                    <p class="mt-3 font-mono text-xs tracking-widest text-cyan-glow uppercase">
                        {{ $projects->count() }} projects
                    </p>
                @endif
            </div>
        </div>

        {{-- Featured: full-width, two columns inside. --}}
        @foreach ($featured as $project)
            <article class="panel panel-sheen spotlight group mt-16 overflow-hidden p-8 lg:p-12"
                     data-reveal="up" data-spotlight>

                <div class="grid gap-10 lg:grid-cols-12">

                    <div class="lg:col-span-7">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full border border-cyan-glow/40 bg-cyan-glow/10 px-3 py-1
                                         font-mono text-[10px] tracking-widest text-cyan-glow uppercase">
                                Featured
                            </span>
                            <span class="font-mono text-xs tracking-widest text-faint uppercase">
                                {{ $project->kind }}
                            </span>
                        </div>

                        <h3 class="mt-5 text-3xl font-semibold tracking-tight text-bright transition-colors
                                   duration-500 group-hover:text-cyan-glow lg:text-5xl">
                            {{ $project->title }}
                        </h3>

                        <p class="mt-5 max-w-xl text-lg leading-relaxed text-muted">{{ $project->summary }}</p>

                        <div class="mt-7 flex flex-wrap items-center gap-2">
                            @foreach ($project->stack ?? [] as $tech)
                                <span class="chip">{{ $tech }}</span>
                            @endforeach
                        </div>

                        @include('sections.partials.project-links', ['project' => $project])
                    </div>

                    {{-- Metrics stack vertically here rather than in a row. --}}
                    <div class="lg:col-span-5">
                        <div class="flex items-baseline justify-between border-b border-edge pb-4">
                            <span class="font-mono text-[10px] tracking-widest text-faint uppercase">What it does</span>
                            <span class="font-mono text-xs text-faint">{{ $project->year }}</span>
                        </div>

                        <dl class="mt-2">
                            @foreach ($project->metrics ?? [] as $metric)
                                <div class="flex items-center gap-4 border-b border-edge/60 py-4">
                                    <span class="font-mono text-[10px] text-faint">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="font-mono text-lg text-bright">{{ $metric }}</span>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </article>
        @endforeach

        {{-- The rest, in a two-column grid. --}}
        <div id="project-grid" class="mt-6 grid gap-6 lg:grid-cols-2">
            @foreach ($rest as $project)
                @php
                    $accent = $accents[$loop->index % count($accents)];
                    $collapsed = $hasMore && $loop->index >= $visibleCount;
                @endphp

                <article @class([
                            'panel panel-sheen spotlight group flex flex-col p-8 lg:p-10',
                            'hidden' => $collapsed,
                         ])
                         @if ($collapsed) data-project-extra @else data-reveal="up" @endif
                         data-tilt data-spotlight>

                    <div class="flex items-start justify-between gap-6">
                        <div>
                            <h3 class="text-2xl font-semibold tracking-tight text-bright transition-colors
                                       duration-500 {{ $accent['text'] }}">
                                {{ $project->title }}
                            </h3>
                            <p class="mt-1 font-mono text-xs tracking-widest text-faint uppercase">
                                {{ $project->kind }}
                            </p>
                        </div>
                        <span class="shrink-0 font-mono text-xs text-faint">{{ $project->year }}</span>
                    </div>

                    <p class="mt-6 flex-1 leading-relaxed text-muted">{{ $project->summary }}</p>

                    @if (filled($project->metrics))
                        <div class="mt-8 grid grid-cols-3 gap-px overflow-hidden rounded-xl border border-edge bg-edge">
                            @foreach ($project->metrics as $metric)
                                <div class="bg-void px-3 py-3 text-center">
                                    <span class="font-mono text-xs text-bright">{{ $metric }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6 flex flex-wrap items-center gap-2">
                        @foreach ($project->stack ?? [] as $tech)
                            <span class="chip">{{ $tech }}</span>
                        @endforeach
                    </div>

                    @include('sections.partials.project-links', ['project' => $project])
                </article>
            @endforeach
        </div>

        @if ($hasMore)
            <div class="mt-12 flex justify-center" data-reveal="fade">
                <button type="button" id="show-more-projects" class="btn-glow" data-magnetic
                        data-label-more="Show all {{ $projects->count() }} projects"
                        data-label-less="Show fewer">
                    <span data-show-more-label>Show all {{ $projects->count() }} projects</span>
                    <span class="transition-transform duration-300" data-show-more-icon
                          aria-hidden="true">&darr;</span>
                </button>
            </div>
        @endif
    </div>
</section>
