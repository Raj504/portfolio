<?php

namespace App\Support;

use App\Models\Visit;
use App\Models\VisitEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read-side aggregation for the admin analytics page.
 *
 * Every method is scoped to a window so the dashboard can offer 7/30/90 day
 * views without each query re-deriving the date maths.
 */
class Analytics
{
    public function __construct(protected CarbonInterface $since)
    {
    }

    /* ------------------------------------------------------------ headline */

    public function summary(): array
    {
        $visits = Visit::since($this->since);
        $engaged = (clone $visits)->engaged();

        $total = (clone $visits)->count();
        $engagedCount = (clone $engaged)->count();

        return [
            'Visits' => $total,
            'Engaged' => $engagedCount,
            'Avg. time' => $this->humanSeconds((int) round((clone $engaged)->avg('duration') ?? 0)),
            'Avg. scroll' => round((clone $engaged)->avg('max_scroll') ?? 0) . '%',
        ];
    }

    /* ------------------------------------------------------------ sections */

    /**
     * Total reading time per section, plus how many visits reached each one.
     * This is the "where do people actually spend time" answer.
     */
    public function sectionTime(): Collection
    {
        return VisitEvent::query()
            ->select('target')
            ->selectRaw('SUM(value) as seconds')
            ->selectRaw('COUNT(DISTINCT visit_id) as visits')
            ->where('type', 'section_time')
            ->whereIn('visit_id', $this->visitIds())
            ->groupBy('target')
            ->orderByDesc('seconds')
            ->get()
            ->map(fn ($row) => [
                'label' => ucfirst($row->target),
                'seconds' => (int) $row->seconds,
                'visits' => (int) $row->visits,
                // Mean time per visitor who actually saw the section.
                'average' => $row->visits > 0 ? (int) round($row->seconds / $row->visits) : 0,
            ]);
    }

    /* -------------------------------------------------------------- clicks */

    public function clicks(): Collection
    {
        return VisitEvent::query()
            ->select('target')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(DISTINCT visit_id) as visits')
            ->where('type', 'click')
            ->whereIn('visit_id', $this->visitIds())
            ->groupBy('target')
            ->orderByDesc('total')
            ->limit(25)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->target,
                'total' => (int) $row->total,
                'visits' => (int) $row->visits,
            ]);
    }

    /**
     * Project link clicks only, split into live vs repo.
     */
    public function projectClicks(): Collection
    {
        return $this->clicks()
            ->filter(fn ($row) => str_starts_with($row['label'], 'project:'))
            ->map(function ($row) {
                // Stored as "project:<title>:<live|repo>".
                [, $title, $kind] = array_pad(explode(':', $row['label'], 3), 3, '');

                return [
                    'title' => $title,
                    'kind' => $kind === 'live' ? 'Live site' : 'Source code',
                    'total' => $row['total'],
                ];
            })
            ->values();
    }

    /* ------------------------------------------------------------- funnels */

    /**
     * How far down the page people get.
     */
    public function scrollFunnel(): Collection
    {
        $total = Visit::since($this->since)->engaged()->count();

        return collect([25, 50, 75, 100])->map(function ($depth) use ($total) {
            $reached = Visit::since($this->since)->engaged()->where('max_scroll', '>=', $depth)->count();

            return [
                'label' => $depth . '%',
                'visits' => $reached,
                'percent' => $total > 0 ? round($reached / $total * 100) : 0,
            ];
        });
    }

    /* ------------------------------------------------------------ audience */

    public function referrers(): Collection
    {
        return $this->breakdown('referrer_host', 'Direct');
    }

    public function devices(): Collection
    {
        return $this->breakdown('device');
    }

    public function browsers(): Collection
    {
        return $this->breakdown('browser');
    }

    /**
     * Visits per day, for the sparkline.
     */
    public function daily(): Collection
    {
        $rows = Visit::since($this->since)
            ->selectRaw('DATE(started_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        // Fill gaps so quiet days render as zero rather than disappearing.
        $days = collect();
        $cursor = $this->since->copy()->startOfDay();

        while ($cursor->lte(now())) {
            $key = $cursor->toDateString();
            $days->push(['day' => $cursor->copy(), 'total' => (int) ($rows[$key] ?? 0)]);
            $cursor->addDay();
        }

        return $days;
    }

    public function recent(int $limit = 15): Collection
    {
        return Visit::since($this->since)->latest('started_at')->limit($limit)->get();
    }

    /* ------------------------------------------------------------- helpers */

    protected function breakdown(string $column, string $nullLabel = 'Unknown'): Collection
    {
        $total = Visit::since($this->since)->count();

        return Visit::since($this->since)
            ->select($column)
            ->selectRaw('COUNT(*) as total')
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->{$column} ?: $nullLabel,
                'total' => (int) $row->total,
                'percent' => $total > 0 ? round($row->total / $total * 100) : 0,
            ]);
    }

    /**
     * Subquery of visit ids in the window, so event queries stay scoped.
     */
    protected function visitIds(): \Illuminate\Database\Query\Builder
    {
        return DB::table('visits')
            ->select('id')
            ->where('started_at', '>=', $this->since);
    }

    public function humanSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }

        return intdiv($seconds, 60) . 'm ' . ($seconds % 60) . 's';
    }
}
