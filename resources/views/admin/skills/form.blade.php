@extends('admin.layout')

@section('title', $group->exists ? 'Edit group' : 'New group')
@section('heading', $group->exists ? 'Edit skill group' : 'New skill group')

@section('content')
    <form method="POST"
          action="{{ $group->exists ? route('admin.skills.update', $group) : route('admin.skills.store') }}"
          class="space-y-8">
        @csrf
        @if ($group->exists) @method('PUT') @endif

        <div class="panel space-y-6 p-8">
            <x-admin.input name="name" label="Group name" :value="$group->name" required
                           hint="e.g. Data & Storage" />

            <x-admin.input name="skills" :value="$group->exists ? $group->skills->pluck('name')->implode(', ') : ''"
                           hint="Comma separated. Saving replaces the whole list." />

            <x-admin.input name="sort_order" type="number" :value="$group->sort_order ?? 0"
                           hint="Lower numbers appear first." />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-glow">{{ $group->exists ? 'Save changes' : 'Create group' }}</button>
            <a href="{{ route('admin.skills.index') }}"
               class="font-mono text-xs tracking-widest text-faint uppercase hover:text-bright">Cancel</a>
        </div>
    </form>
@endsection
