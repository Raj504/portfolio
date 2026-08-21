@extends('admin.layout')

@section('title', $experience->exists ? 'Edit role' : 'New role')
@section('heading', $experience->exists ? 'Edit role' : 'New role')

@section('content')
    <form method="POST"
          action="{{ $experience->exists ? route('admin.experiences.update', $experience) : route('admin.experiences.store') }}"
          class="space-y-8">
        @csrf
        @if ($experience->exists) @method('PUT') @endif

        <div class="panel space-y-6 p-8">
            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="role" :value="$experience->role" required />
                <x-admin.input name="company" :value="$experience->company" required />
                <x-admin.input name="period" :value="$experience->period" required
                               hint="e.g. 2024 - Present" />
                <x-admin.input name="sort_order" type="number" :value="$experience->sort_order ?? 0"
                               hint="Lower numbers appear first." />
            </div>

            <x-admin.input name="points" type="textarea" rows="6"
                           :value="implode(PHP_EOL, $experience->points ?? [])"
                           label="Bullet points" hint="One per line." />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-glow">{{ $experience->exists ? 'Save changes' : 'Create role' }}</button>
            <a href="{{ route('admin.experiences.index') }}"
               class="font-mono text-xs tracking-widest text-faint uppercase hover:text-bright">Cancel</a>
        </div>
    </form>
@endsection
