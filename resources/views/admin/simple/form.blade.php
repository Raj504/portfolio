{{-- Shared form for socials, stats and principles. --}}
@extends('admin.layout')

@section('title', ($item->exists ? 'Edit ' : 'New ') . Str::lower($label))
@section('heading', ($item->exists ? 'Edit ' : 'New ') . Str::lower($label))

@section('content')
    <form method="POST"
          action="{{ $item->exists ? route($route . '.update', $item->id) : route($route . '.store') }}"
          class="space-y-8">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div class="panel space-y-6 p-8">
            @foreach ($fields as $field)
                <x-admin.input :name="$field"
                               :type="$field === 'body' ? 'textarea' : 'text'"
                               :value="$item->{$field}"
                               required />
            @endforeach

            <x-admin.input name="sort_order" type="number" :value="$item->sort_order ?? 0"
                           hint="Lower numbers appear first." />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-glow">{{ $item->exists ? 'Save changes' : 'Create' }}</button>
            <a href="{{ route($route . '.index') }}"
               class="font-mono text-xs tracking-widest text-faint uppercase hover:text-bright">Cancel</a>
        </div>
    </form>
@endsection
