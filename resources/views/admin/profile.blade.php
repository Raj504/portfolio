@extends('admin.layout')

@section('title', 'Profile')
@section('heading', 'Profile')

@section('content')
    <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="panel p-8">
            <h2 class="mb-6 font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">Identity</h2>

            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="name" :value="$profile->name" required />
                <x-admin.input name="role" :value="$profile->role" required />
            </div>

            <div class="mt-6 space-y-6">
                <x-admin.input name="tagline" :value="$profile->tagline" required
                               hint="Short line used in social previews." />
                <x-admin.input name="blurb" type="textarea" rows="4" :value="$profile->blurb" required
                               hint="The paragraph under the hero headline." />
            </div>
        </div>

        <div class="panel p-8">
            <h2 class="mb-6 font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">Contact</h2>

            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="email" type="email" :value="$profile->email" required />
                <x-admin.input name="phone" :value="$profile->phone"
                               hint="Shown in About and Contact. Leave blank to hide." />
                <x-admin.input name="location" :value="$profile->location" required />
            </div>
        </div>

        <div class="panel p-8">
            <h2 class="mb-6 font-mono text-xs tracking-[0.25em] text-cyan-glow uppercase">Availability</h2>

            <x-admin.input name="available" type="checkbox" label="Open to work"
                           :value="$profile->available"
                           hint="Shows the pulsing badge in the nav and the About card." />

            <fieldset class="mt-6">
                <legend class="mb-3 font-mono text-[10px] tracking-widest text-faint uppercase">
                    Work arrangement
                </legend>

                @php
                    $options = ['Remote', 'Hybrid', 'On-site', 'Open to relocation'];
                    $selected = old('availability_modes', $profile->availability_modes ?? []);
                @endphp

                <div class="flex flex-wrap gap-3">
                    @foreach ($options as $option)
                        <label class="flex cursor-pointer items-center gap-2 rounded-full border border-edge
                                      bg-void px-4 py-2 transition-colors hover:border-cyan-glow/40">
                            <input type="checkbox" name="availability_modes[]" value="{{ $option }}"
                                   @checked(in_array($option, $selected, true))
                                   class="h-3.5 w-3.5 rounded border-edge bg-void accent-cyan-glow">
                            <span class="font-mono text-xs text-muted">{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="mt-6">
                <x-admin.input name="availability_note" :value="$profile->availability_note"
                               label="Note" hint="One line under the arrangement chips." />
            </div>
        </div>

        <button type="submit" class="btn-glow">Save profile</button>
    </form>
@endsection
