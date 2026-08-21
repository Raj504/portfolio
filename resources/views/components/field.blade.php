@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
])

@php
    $id = 'field-' . $name;
    $invalid = $errors->has($name);

    $base = 'peer w-full rounded-xl border bg-void px-4 py-3.5 text-bright placeholder-transparent
             transition-colors duration-300 outline-none focus:border-cyan-glow';

    $border = $invalid ? 'border-red-500/60' : 'border-edge';
@endphp

<div class="relative">
    @if ($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="5"
            placeholder="{{ $label }}"
            @class([$base, $border, 'resize-none'])
            @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        >{{ $value }}</textarea>
    @else
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ $value }}"
            placeholder="{{ $label }}"
            @class([$base, $border])
            @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        >
    @endif

    {{-- Floating label: sits inside the field until it is focused or filled. --}}
    <label for="{{ $id }}"
           class="pointer-events-none absolute top-0 left-3 -translate-y-1/2 bg-slate-panel px-1.5
                  font-mono text-[10px] tracking-widest text-cyan-glow uppercase transition-all duration-300
                  peer-placeholder-shown:top-3.5 peer-placeholder-shown:translate-y-0
                  peer-placeholder-shown:bg-transparent peer-placeholder-shown:text-xs
                  peer-placeholder-shown:normal-case peer-placeholder-shown:tracking-normal
                  peer-placeholder-shown:text-faint
                  peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:bg-slate-panel
                  peer-focus:text-[10px] peer-focus:tracking-widest peer-focus:uppercase
                  peer-focus:text-cyan-glow">
        {{ $label }}
    </label>

    @error($name)
        <p id="{{ $id }}-error" class="mt-2 font-mono text-[11px] text-red-400">{{ $message }}</p>
    @enderror
</div>
