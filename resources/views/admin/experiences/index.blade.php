@extends('admin.layout')

@section('title', 'Experience')
@section('heading', 'Experience')

@section('actions')
    <a href="{{ route('admin.experiences.create') }}" class="btn-glow">New role</a>
@endsection

@section('content')
    @forelse ($experiences as $experience)
        <div class="mb-3 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-edge bg-ink px-5 py-4">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-bright">{{ $experience->role }}</p>
                <p class="mt-1 font-mono text-[11px] text-faint">
                    {{ $experience->company }} &middot; {{ $experience->period }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.experiences.edit', $experience) }}"
                   class="font-mono text-[11px] tracking-widest text-cyan-glow uppercase">Edit</a>

                <form method="POST" action="{{ route('admin.experiences.destroy', $experience) }}"
                      onsubmit="return confirm('Delete this role?')">
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
            No roles yet.
        </p>
    @endforelse
@endsection
