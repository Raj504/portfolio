@extends('admin.layout')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-px overflow-hidden rounded-2xl border border-edge bg-edge sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($counts as $label => $count)
            <div class="bg-ink px-6 py-7">
                <p class="font-mono text-[10px] tracking-widest text-faint uppercase">{{ $label }}</p>
                <p class="mt-2 font-mono text-3xl font-semibold text-bright">{{ $count }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mt-12 mb-4 font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">Recent messages</h2>

    @forelse ($recent as $message)
        <a href="{{ route('admin.messages.show', $message) }}"
           class="mb-2 flex items-center justify-between gap-4 rounded-xl border border-edge bg-ink px-5 py-4
                  transition-colors hover:border-cyan-glow/40">
            <span class="min-w-0">
                <span class="block truncate text-sm text-bright">
                    @if ($message->isUnread())
                        <span class="mr-2 inline-block h-1.5 w-1.5 rounded-full bg-cyan-glow align-middle"></span>
                    @endif
                    {{ $message->subject }}
                </span>
                <span class="block truncate font-mono text-[11px] text-faint">{{ $message->email }}</span>
            </span>
            <span class="shrink-0 font-mono text-[10px] text-faint">{{ $message->created_at->diffForHumans() }}</span>
        </a>
    @empty
        <p class="rounded-xl border border-dashed border-edge px-5 py-8 text-center text-sm text-faint">
            No messages yet.
        </p>
    @endforelse
@endsection
