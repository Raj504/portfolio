@extends('admin.layout')

@section('title', 'Analytics')
@section('heading', 'Analytics')

@section('actions')
    <div class="flex items-center gap-1 rounded-lg border border-edge bg-ink p-1">
        @foreach ($ranges as $value => $label)
            <a href="{{ route('admin.analytics', ['days' => $value]) }}"
               @class([
                   'rounded-md px-3 py-1.5 font-mono text-[10px] tracking-widest uppercase transition-colors',
                   'bg-cyan-glow/15 text-cyan-glow' => $days === $value,
                   'text-faint hover:text-bright' => $days !== $value,
               ])>
                {{ $value }}d
            </a>
        @endforeach
    </div>
@endsection

@section('content')

    {{-- Headline numbers --}}
    <div class="grid gap-px overflow-hidden rounded-2xl border border-edge bg-edge sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($summary as $label => $value)
            <div class="bg-ink px-6 py-7">
                <p class="font-mono text-[10px] tracking-widest text-faint uppercase">{{ $label }}</p>
                <p class="mt-2 font-mono text-3xl font-semibold text-bright">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    @php
        $peak = $daily->max('total') ?: 1;
    @endphp

    {{-- Visits per day --}}
    <section class="panel mt-8 p-8">
        <h2 class="font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">Visits per day</h2>

        <div class="mt-8 flex h-40 items-end gap-1">
            @foreach ($daily as $day)
                <div class="group relative flex-1">
                    <div class="rounded-t bg-linear-to-t from-cyan-glow/30 to-cyan-glow transition-all
                                hover:from-cyan-glow/50"
                         style="height: {{ max(2, round($day['total'] / $peak * 150)) }}px"></div>

                    {{-- Tooltip --}}
                    <span class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden -translate-x-1/2
                                 rounded-md border border-edge bg-void px-2 py-1 font-mono text-[10px]
                                 whitespace-nowrap text-bright group-hover:block">
                        {{ $day['day']->format('d M') }}: {{ $day['total'] }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-3 flex justify-between font-mono text-[10px] text-faint">
            <span>{{ $daily->first()['day']->format('d M') }}</span>
            <span>{{ $daily->last()['day']->format('d M') }}</span>
        </div>
    </section>

    {{-- Where time is spent --}}
    <section class="panel mt-8 p-8">
        <h2 class="font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">Time spent per section</h2>
        <p class="mt-2 text-sm text-muted">Only counts while the tab is open and the section is on screen.</p>

        @php $topSeconds = $sections->max('seconds') ?: 1; @endphp

        <div class="mt-8 space-y-5">
            @forelse ($sections as $section)
                <div>
                    <div class="flex items-baseline justify-between gap-4">
                        <span class="text-sm font-semibold text-bright">{{ $section['label'] }}</span>
                        <span class="font-mono text-xs text-faint">
                            {{ $analytics->humanSeconds($section['seconds']) }} total
                            &middot; {{ $analytics->humanSeconds($section['average']) }} avg
                            &middot; {{ $section['visits'] }} {{ Str::plural('visit', $section['visits']) }}
                        </span>
                    </div>

                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-edge">
                        <div class="h-full rounded-full bg-linear-to-r from-cyan-glow to-violet-glow"
                             style="width: {{ round($section['seconds'] / $topSeconds * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="rounded-xl border border-dashed border-edge px-5 py-8 text-center text-sm text-faint">
                    No section data yet.
                </p>
            @endforelse
        </div>
    </section>

    <div class="mt-8 grid gap-8 lg:grid-cols-2">

        {{-- Scroll funnel --}}
        <section class="panel p-8">
            <h2 class="font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">How far people scroll</h2>

            <div class="mt-8 space-y-4">
                @foreach ($funnel as $step)
                    <div>
                        <div class="flex items-baseline justify-between">
                            <span class="font-mono text-xs text-muted">Reached {{ $step['label'] }}</span>
                            <span class="font-mono text-xs text-bright">
                                {{ $step['visits'] }} ({{ $step['percent'] }}%)
                            </span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-edge">
                            <div class="h-full rounded-full bg-cyan-glow"
                                 style="width: {{ $step['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Project link clicks --}}
        <section class="panel p-8">
            <h2 class="font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">Project links clicked</h2>

            <div class="mt-8 space-y-3">
                @forelse ($projectClicks as $click)
                    <div class="flex items-center justify-between gap-4 border-b border-edge/60 pb-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm text-bright">{{ $click['title'] }}</p>
                            <p class="font-mono text-[10px] tracking-widest text-faint uppercase">
                                {{ $click['kind'] }}
                            </p>
                        </div>
                        <span class="shrink-0 font-mono text-lg text-cyan-glow">{{ $click['total'] }}</span>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-edge px-5 py-8 text-center text-sm text-faint">
                        No project links clicked yet.
                    </p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Everything clicked --}}
    <section class="panel mt-8 p-8">
        <h2 class="font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">Most clicked</h2>

        <div class="mt-8 space-y-2">
            @forelse ($clicks as $click)
                <div class="flex items-center justify-between gap-4 rounded-lg bg-void px-4 py-3">
                    <span class="truncate font-mono text-xs text-muted">{{ $click['label'] }}</span>
                    <span class="shrink-0 font-mono text-xs text-bright">
                        {{ $click['total'] }}
                        <span class="text-faint">/ {{ $click['visits'] }} {{ Str::plural('visitor', $click['visits']) }}</span>
                    </span>
                </div>
            @empty
                <p class="rounded-xl border border-dashed border-edge px-5 py-8 text-center text-sm text-faint">
                    No clicks recorded yet.
                </p>
            @endforelse
        </div>
    </section>

    {{-- Audience breakdowns --}}
    <div class="mt-8 grid gap-8 lg:grid-cols-3">
        @foreach ([
            'Traffic sources' => $referrers,
            'Devices' => $devices,
            'Browsers' => $browsers,
        ] as $heading => $rows)
            <section class="panel p-8">
                <h2 class="font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">{{ $heading }}</h2>

                <div class="mt-6 space-y-3">
                    @forelse ($rows as $row)
                        <div>
                            <div class="flex items-baseline justify-between gap-3">
                                <span class="truncate text-sm text-muted">{{ $row['label'] }}</span>
                                <span class="shrink-0 font-mono text-xs text-bright">{{ $row['total'] }}</span>
                            </div>
                            <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-edge">
                                <div class="h-full rounded-full bg-violet-glow"
                                     style="width: {{ $row['percent'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-faint">No data yet.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    {{-- Recent visits --}}
    <section class="panel mt-8 p-8">
        <h2 class="font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">Recent visits</h2>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[640px] text-left">
                <thead>
                    <tr class="border-b border-edge">
                        @foreach (['When', 'Source', 'Device', 'Browser', 'Time', 'Scroll'] as $column)
                            <th class="pb-3 font-mono text-[10px] tracking-widest text-faint uppercase">
                                {{ $column }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $visit)
                        <tr class="border-b border-edge/50">
                            <td class="py-3 font-mono text-xs text-muted">
                                {{ $visit->started_at->diffForHumans(short: true) }}
                            </td>
                            <td class="py-3 text-xs text-muted">{{ $visit->referrer_host ?: 'Direct' }}</td>
                            <td class="py-3 text-xs text-muted">{{ ucfirst($visit->device ?: '—') }}</td>
                            <td class="py-3 text-xs text-muted">{{ $visit->browser ?: '—' }}</td>
                            <td class="py-3 font-mono text-xs text-bright">
                                {{ $analytics->humanSeconds($visit->duration) }}
                            </td>
                            <td class="py-3 font-mono text-xs text-bright">{{ $visit->max_scroll }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-faint">No visits recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <p class="mt-8 font-mono text-[10px] leading-relaxed text-faint">
        No third-party trackers and no cross-site cookies. IP addresses are stored only as a daily salted hash,
        the session id lives in sessionStorage and dies with the tab, and browsers sending Do Not Track or
        Global Privacy Control are skipped entirely. Your own visits are not counted while signed in.
    </p>
@endsection
