<footer class="relative border-t border-edge/60 px-6 py-12 lg:px-10">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 sm:flex-row">

        <p class="font-mono text-xs tracking-wider text-faint">
            &copy; {{ date('Y') }} {{ $profile->name }}. Built with Laravel, Three.js &amp; GSAP.
        </p>

        <div class="flex items-center gap-6">
            @foreach ($site->socials() as $social)
                <a href="{{ $social->url }}" target="_blank" rel="noopener noreferrer"
                   class="link-wipe font-mono text-xs tracking-widest uppercase">
                    {{ $social->label }}
                </a>
            @endforeach
        </div>

        <a href="#top" class="group flex items-center gap-2 font-mono text-xs tracking-widest text-faint uppercase
                              transition-colors hover:text-cyan-glow">
            Back to top
            <span class="transition-transform duration-300 group-hover:-translate-y-1">&uarr;</span>
        </a>
    </div>
</footer>
