@extends('admin.layout')

@section('title', 'Message')
@section('heading', 'Message')

@section('actions')
    <a href="{{ route('admin.messages.index') }}"
       class="font-mono text-xs tracking-widest text-faint uppercase hover:text-bright">&larr; All messages</a>
@endsection

@section('content')
    <div class="panel p-8">
        <h2 class="text-xl font-semibold text-bright">{{ $message->subject }}</h2>

        <dl class="mt-6 grid gap-4 border-y border-edge py-5 sm:grid-cols-3">
            <div>
                <dt class="font-mono text-[10px] tracking-widest text-faint uppercase">From</dt>
                <dd class="mt-1 text-sm text-bright">{{ $message->name }}</dd>
            </div>
            <div>
                <dt class="font-mono text-[10px] tracking-widest text-faint uppercase">Email</dt>
                <dd class="mt-1 truncate text-sm">
                    <a href="mailto:{{ $message->email }}" class="text-cyan-glow hover:underline">
                        {{ $message->email }}
                    </a>
                </dd>
            </div>
            <div>
                <dt class="font-mono text-[10px] tracking-widest text-faint uppercase">Received</dt>
                <dd class="mt-1 text-sm text-muted">{{ $message->created_at->format('d M Y, H:i') }}</dd>
            </div>
        </dl>

        <p class="mt-6 leading-relaxed whitespace-pre-line text-muted">{{ $message->message }}</p>

        <div class="mt-8 flex flex-wrap items-center gap-4 border-t border-edge pt-6">
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: ' . $message->subject) }}"
               class="btn-glow">Reply by email</a>

            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}"
                  onsubmit="return confirm('Delete this message permanently?')">
                @csrf
                @method('DELETE')
                <button class="font-mono text-xs tracking-widest text-faint uppercase hover:text-red-400">
                    Delete
                </button>
            </form>
        </div>
    </div>
@endsection
