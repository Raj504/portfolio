@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'rows' => 4,
    'required' => false,
])

@php
    $id = 'f-' . $name;
    $label ??= Str::headline($name);
    $current = old($name, $value);
    $invalid = $errors->has($name);

    $classes = 'w-full rounded-lg border bg-void px-4 py-2.5 text-sm text-bright
                transition-colors outline-none focus:border-cyan-glow '
             . ($invalid ? 'border-red-500/60' : 'border-edge');
@endphp

<div>
    <label for="{{ $id }}" class="mb-2 block font-mono text-[10px] tracking-widest text-faint uppercase">
        {{ $label }}@if ($required)<span class="text-cyan-glow"> *</span>@endif
    </label>

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
                  class="{{ $classes }} resize-y">{{ $current }}</textarea>
    @elseif ($type === 'checkbox')
        <label class="flex cursor-pointer items-center gap-3">
            {{-- Hidden 0 first so an unchecked box still posts a value. --}}
            <input type="hidden" name="{{ $name }}" value="0">
            <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="1"
                   @checked($current)
                   class="h-4 w-4 rounded border-edge bg-void accent-cyan-glow">
            <span class="text-sm text-muted">{{ $hint ?? 'Enabled' }}</span>
        </label>
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}"
               value="{{ $current }}" class="{{ $classes }}">
    @endif

    @if ($hint && $type !== 'checkbox')
        <p class="mt-1.5 font-mono text-[10px] text-faint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 font-mono text-[11px] text-red-400">{{ $message }}</p>
    @enderror
</div>
