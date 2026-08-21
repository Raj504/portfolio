@extends('admin.layout')

@section('title', $project->exists ? 'Edit project' : 'New project')
@section('heading', $project->exists ? 'Edit project' : 'New project')

@section('content')
    <form method="POST"
          action="{{ $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store') }}"
          class="space-y-8">
        @csrf
        @if ($project->exists) @method('PUT') @endif

        <div class="panel space-y-6 p-8">
            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="title" :value="$project->title" required />
                <x-admin.input name="kind" label="Kind" :value="$project->kind" required
                               hint="e.g. Webhook delivery platform" />
                <x-admin.input name="year" :value="$project->year" required />
            </div>

            <x-admin.input name="summary" type="textarea" rows="4" :value="$project->summary" required />

            {{-- Both links are optional and render independently on the card. --}}
            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="live_url" type="url" label="Live link" :value="$project->live_url"
                               hint="Deployed site or demo. Leave blank if there is none." />
                <x-admin.input name="repo_url" type="url" label="Repository link" :value="$project->repo_url"
                               hint="GitHub, GitLab, Bitbucket. Leave blank if private." />

            <x-admin.input name="stack" :value="implode(', ', $project->stack ?? [])"
                           hint="Comma separated. e.g. Laravel, Redis, Docker" />

            <x-admin.input name="metrics" label="Highlights" :value="implode(', ', $project->metrics ?? [])"
                           hint="Comma separated, three work best. Capabilities or real numbers — only what you can back up. e.g. Passes from 1 day, UPI payments, QR check-in" />

            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="sort_order" type="number" :value="$project->sort_order ?? 0"
                               hint="Lower numbers appear first." />
                <x-admin.input name="published" type="checkbox" label="Visible on the site"
                               :value="$project->exists ? $project->published : true" hint="Show this project" />
                <x-admin.input name="featured" type="checkbox" label="Feature this project"
                               :value="$project->featured"
                               hint="Renders as a wide hero card at the top of the work grid." />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-glow">{{ $project->exists ? 'Save changes' : 'Create project' }}</button>
            <a href="{{ route('admin.projects.index') }}"
               class="font-mono text-xs tracking-widest text-faint uppercase hover:text-bright">Cancel</a>
        </div>
    </form>
@endsection
