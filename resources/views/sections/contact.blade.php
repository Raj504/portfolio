<section id="contact" class="relative px-6 py-28 lg:px-10 lg:py-40">

    <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-[5] h-96
                bg-linear-to-t from-cyan-glow/5 to-transparent" aria-hidden="true"></div>

    <div class="mx-auto max-w-7xl">

        <div class="grid gap-16 lg:grid-cols-12">

            <div class="lg:col-span-5">
                <p class="eyebrow" data-reveal="fade">05 / Contact</p>

                <h2 class="mt-6 text-[clamp(2.5rem,6vw,4.5rem)] leading-[0.95] font-semibold tracking-tight"
                    data-reveal="up">
                    Got something<br>
                    <span class="bg-linear-to-r from-cyan-glow to-violet-glow bg-clip-text text-transparent">
                        heavy to build?
                    </span>
                </h2>

                <p class="mt-8 max-w-md leading-relaxed text-muted" data-reveal="up">
                    Open to backend and platform roles, and to consulting on systems that have outgrown
                    their original design. Send a note and I will reply within a couple of days.
                </p>

                <div class="mt-10 space-y-4" data-reveal="up">
                    <a href="mailto:{{ $profile->email }}"
                       class="block font-mono text-lg text-bright transition-colors hover:text-cyan-glow"
                       data-magnetic>
                        {{ $profile->email }}
                    </a>

                    @if ($profile->phone)
                        {{-- tel: needs the bare number; the label keeps the spacing. --}}
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $profile->phone) }}"
                           class="block font-mono text-lg text-bright transition-colors hover:text-cyan-glow"
                           data-magnetic>
                            {{ $profile->phone }}
                        </a>
                    @endif

                    <div class="flex flex-wrap gap-6">
                        @foreach ($site->socials() as $social)
                            <a href="{{ $social->url }}" target="_blank" rel="noopener noreferrer"
                               class="link-wipe font-mono text-xs tracking-widest uppercase">
                                {{ $social->label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Message form --}}
            <div class="lg:col-span-7">
                <div class="panel panel-sheen p-8 lg:p-10" data-reveal="up">

                    @if (session('contact.success'))
                        <div class="mb-8 flex items-start gap-3 rounded-xl border border-cyan-glow/30
                                    bg-cyan-glow/10 px-5 py-4">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-cyan-glow"></span>
                            <p class="text-sm text-cyan-glow">{{ session('contact.success') }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
                        @csrf

                        {{-- Honeypot: real users never fill this in. --}}
                        <div class="absolute -left-[9999px]" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <x-field name="name" label="Name" :value="old('name')" />
                            <x-field name="email" label="Email" type="email" :value="old('email')" />
                        </div>

                        <x-field name="subject" label="Subject" :value="old('subject')" />
                        <x-field name="message" label="Message" type="textarea" :value="old('message')" />

                        <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
                            <p class="font-mono text-[11px] tracking-wide text-faint">
                                Rate limited. Never shared.
                            </p>
                            <button type="submit" class="btn-glow" data-magnetic>
                                Send message
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
