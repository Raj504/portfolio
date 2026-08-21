@extends('admin.layout')

@section('title', 'Projects')
@section('heading', 'Projects')

@section('actions')
    <a href="{{ route('admin.projects.create') }}" class="btn-glow">New project</a>
@endsection

@section('content')
    @forelse ($projects as $project)
        <div class="mb-3 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-edge bg-ink px-5 py-4">
            <div class="min-w-0">
                <p class="flex items-center gap-3 text-sm font-semibold text-bright">
                    {{ $project->title }}
                    @if ($project->featured)
                        <span class="rounded-full border border-cyan-glow/40 bg-cyan-glow/10 px-2 py-0.5
                                     font-mono text-[10px] text-cyan-glow">
                            Featured
                        </span>
                    @endif
                    @unless ($project->published)
                        <span class="rounded-full border border-edge px-2 py-0.5 font-mono text-[10px] text-faint">
                            Hidden
                        </span>
                    @endunless
                </p>
                <p class="mt-1 font-mono text-[11px] text-faint">{{ $project->kind }} &middot; {{ $project->year }}</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.projects.edit', $project) }}"
                   class="font-mono text-[11px] tracking-widest text-cyan-glow uppercase">Edit</a>

                <form method="POST" action="{{ route('admin.projects.destroy', $project) }}"
                      onsubmit="return confirm('Delete {{ addslashes($project->title) }}?')">
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
            No projects yet.
        </p>
    @endforelse
@endsection
