<?php

namespace App\Http\Controllers\Admin;

use App\Support\Analytics;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AnalyticsController extends AdminController
{
    /** Selectable windows, in days. */
    protected const RANGES = [7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'];

    public function __invoke(Request $request): View
    {
        $days = (int) $request->query('days', 30);

        if (! array_key_exists($days, self::RANGES)) {
            $days = 30;
        }

        $analytics = new Analytics(now()->subDays($days)->startOfDay());

        return view('admin.analytics', [
            'days' => $days,
            'ranges' => self::RANGES,
            'summary' => $analytics->summary(),
            'sections' => $analytics->sectionTime(),
            'clicks' => $analytics->clicks(),
            'projectClicks' => $analytics->projectClicks(),
            'funnel' => $analytics->scrollFunnel(),
            'referrers' => $analytics->referrers(),
            'devices' => $analytics->devices(),
            'browsers' => $analytics->browsers(),
            'daily' => $analytics->daily(),
            'recent' => $analytics->recent(),
            'analytics' => $analytics,
        ]);
    }
}
