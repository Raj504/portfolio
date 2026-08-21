{{-- Shared list view for socials, status items and principles. --}}
@extends('admin.layout')

@section('title', Str::plural($label))
@section('heading', Str::plural($label))

@section('actions')
    <a href="{{ route($route . '.create') }}" class="btn-glow">New {{ Str::lower($label) }}</a>
@endsection

@section('content')
    @forelse ($items as $item)
        <div class="mb-3 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-edge bg-ink px-5 py-4">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-bright">{{ $item->{$fields[0]} }}</p>
                @if (count($fields) > 1)
                    <p class="mt-1 truncate font-mono text-[11px] text-faint">{{ $item->{$fields[1]} }}</p>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route($route . '.edit', $item->id) }}"
                   class="font-mono text-[11px] tracking-widest text-cyan-glow uppercase">Edit</a>

                <form method="POST" action="{{ route($route . '.destroy', $item->id) }}"
                      onsubmit="return confirm('Delete this {{ Str::lower($label) }}?')">
                    @csrf
                    @method('DELETE')
                    <button class="font-mono text-[11px] tracking-widest text-faint uppercase hover:text-red-400">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <p class="rounded-xl border border-dashed border-edge px-5 py-10 text-center text-sm text-faint">
            Nothing here yet.
        </p>
    @endforelse
@endsection
