@extends('admin.layout')

@section('title', 'Skills')
@section('heading', 'Skills')

@section('actions')
    <a href="{{ route('admin.skills.create') }}" class="btn-glow">New group</a>
@endsection

@section('content')
    @forelse ($groups as $group)
        <div class="mb-3 rounded-xl border border-edge bg-ink px-5 py-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm font-semibold text-bright">{{ $group->name }}</p>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.skills.edit', $group) }}"
                       class="font-mono text-[11px] tracking-widest text-cyan-glow uppercase">Edit</a>

                    <form method="POST" action="{{ route('admin.skills.destroy', $group) }}"
                          onsubmit="return confirm('Delete this group and its skills?')">
                        @csrf
                        @method('DELETE')
                        <button class="font-mono text-[11px] tracking-widest text-faint uppercase hover:text-red-400">
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($group->skills as $skill)
                    <span class="chip">{{ $skill->name }}</span>
                @endforeach
            </div>
        </div>
    @empty
        <p class="rounded-xl border border-dashed border-edge px-5 py-10 text-center text-sm text-faint">
            No skill groups yet.
        </p>
    @endforelse
@endsection
