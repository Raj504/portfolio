@extends('admin.layout')

@section('title', 'Messages')
@section('heading', 'Messages')

@section('content')
    @forelse ($messages as $message)
        <a href="{{ route('admin.messages.show', $message) }}"
           class="mb-2 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-edge bg-ink px-5 py-4
                  transition-colors hover:border-cyan-glow/40">
            <span class="min-w-0">
                <span class="block truncate text-sm {{ $message->isUnread() ? 'font-semibold text-bright' : 'text-muted' }}">
                    @if ($message->isUnread())
                        <span class="mr-2 inline-block h-1.5 w-1.5 rounded-full bg-cyan-glow align-middle"></span>
                    @endif
                    {{ $message->subject }}
                </span>
                <span class="block truncate font-mono text-[11px] text-faint">
                    {{ $message->name }} &middot; {{ $message->email }}
                </span>
            </span>
            <span class="shrink-0 font-mono text-[10px] text-faint">
                {{ $message->created_at->format('d M Y, H:i') }}
            </span>
        </a>
    @empty
        <p class="rounded-xl border border-dashed border-edge px-5 py-10 text-center text-sm text-faint">
            No messages yet.
        </p>
    @endforelse

    <div class="mt-8">{{ $messages->links() }}</div>
@endsection
