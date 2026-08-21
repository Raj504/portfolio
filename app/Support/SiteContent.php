<?php

namespace App\Support;

use App\Models\Experience;
use App\Models\Principle;
use App\Models\Profile;
use App\Models\Project;
use App\Models\SkillGroup;
use App\Models\Social;
use App\Models\Stat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Read model for the public site.
 *
 * The homepage is the same handful of queries on every request and only
 * changes when the admin saves something, so each list is cached until the
 * admin panel calls flush().
 */
class SiteContent
{
    /**
     * Cache keys owned by this class. Listed so flush() cannot drift.
     */
    protected const KEYS = [
        'site.profile',
        'site.socials',
        'site.stats',
        'site.principles',
        'site.skills',
        'site.projects',
        'site.experiences',
    ];

    public function profile(): Profile
    {
        return $this->remember('site.profile', fn () => Profile::current());
    }

    public function socials(): Collection
    {
        return $this->remember('site.socials', fn () => Social::ordered()->get());
    }

    public function stats(): Collection
    {
        return $this->remember('site.stats', fn () => Stat::ordered()->get());
    }

    public function principles(): Collection
    {
        return $this->remember('site.principles', fn () => Principle::ordered()->get());
    }

    public function skillGroups(): Collection
    {
        return $this->remember('site.skills', fn () => SkillGroup::with('skills')->ordered()->get());
    }

    public function projects(): Collection
    {
        return $this->remember('site.projects', fn () => Project::published()->ordered()->get());
    }

    public function experiences(): Collection
    {
        return $this->remember('site.experiences', fn () => Experience::ordered()->get());
    }

    /**
     * Flat list of skill names, used by the hero marquee.
     */
    public function marqueeItems(int $limit = 14): Collection
    {
        return $this->skillGroups()
            ->flatMap(fn (SkillGroup $group) => $group->skills->pluck('name'))
            ->take($limit);
    }

    /**
     * Drop every cached list. Called whenever the admin writes.
     */
    public static function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
        }
    }

    protected function remember(string $key, callable $callback): mixed
    {
        return Cache::rememberForever($key, $callback);
    }
}
