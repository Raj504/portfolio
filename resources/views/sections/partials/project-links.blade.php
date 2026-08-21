{{-- Live and repo links. Independent: a project may have one, both or neither. --}}
@if (filled($project->live_url) || filled($project->repo_url))
    <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-edge pt-6">

        @if (filled($project->live_url))
            <a href="{{ $project->live_url }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 rounded-full border border-cyan-glow/40
                      bg-cyan-glow/10 px-4 py-2 font-mono text-[11px] tracking-widest
                      text-cyan-glow uppercase transition-colors hover:bg-cyan-glow/20"
               aria-label="Open {{ $project->title }} live site">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5" aria-hidden="true">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                    <polyline points="15 3 21 3 21 9"/>
                    <line x1="10" y1="14" x2="21" y2="3"/>
                </svg>
                Live site
            </a>
        @endif

        @if (filled($project->repo_url))
            <a href="{{ $project->repo_url }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 rounded-full border border-edge
                      px-4 py-2 font-mono text-[11px] tracking-widest text-muted uppercase
                      transition-colors hover:border-violet-glow/50 hover:text-violet-glow"
               aria-label="Open {{ $project->title }} source code">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5" aria-hidden="true">
                    <polyline points="16 18 22 12 16 6"/>
                    <polyline points="8 6 2 12 8 18"/>
                </svg>
                Source code
            </a>
        @endif
    </div>
@endif
