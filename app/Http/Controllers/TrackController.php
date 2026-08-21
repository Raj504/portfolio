<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\VisitEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Receives batched analytics beacons from the public site.
 *
 * The browser sends deltas, so this endpoint is called several times per
 * visit: once shortly after load and again whenever the tab is hidden or
 * closed. Everything is additive, which keeps it safe to retry.
 */
class TrackController extends Controller
{
    /** Event types the client is allowed to record. */
    protected const TYPES = ['section_time', 'click', 'scroll_depth'];

    /** Guards against a runaway client inflating a single session. */
    protected const MAX_EVENT_SECONDS = 1800;

    public function store(Request $request): JsonResponse
    {
        // Never record the owner browsing their own site.
        if (Auth::check()) {
            return response()->json(['ok' => true, 'ignored' => 'authenticated']);
        }

        // Honour Do Not Track and Global Privacy Control.
        if ($request->header('DNT') === '1' || $request->header('Sec-GPC') === '1') {
            return response()->json(['ok' => true, 'ignored' => 'dnt']);
        }

        $data = $request->validate([
            'sid' => ['required', 'string', 'size:32', 'alpha_num'],
            'path' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string', 'max:500'],
            'screen' => ['nullable', 'string', 'max:20'],
            'duration' => ['nullable', 'integer', 'min:0', 'max:' . self::MAX_EVENT_SECONDS],
            'max_scroll' => ['nullable', 'integer', 'min:0', 'max:100'],
            'events' => ['nullable', 'array', 'max:100'],
            'events.*.type' => ['required', 'string', 'in:' . implode(',', self::TYPES)],
            'events.*.target' => ['required', 'string', 'max:120'],
            'events.*.value' => ['nullable', 'integer', 'min:0', 'max:' . self::MAX_EVENT_SECONDS],
        ]);

        $visit = Visit::firstOrCreate(
            ['session_id' => $data['sid']],
            [
                'ip_hash' => $this->hashIp($request->ip()),
                'path' => $data['path'] ?? '/',
                'referrer' => $data['referrer'] ?: null,
                'referrer_host' => $this->host($data['referrer'] ?? null),
                'screen' => $data['screen'] ?? null,
                'device' => $this->device($request->userAgent()),
                'browser' => $this->browser($request->userAgent()),
                'platform' => $this->platform($request->userAgent()),
                'started_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        // Duration and scroll depth are cumulative totals, not deltas, so the
        // highest value wins rather than summing duplicate beacons.
        $visit->forceFill([
            'duration' => max($visit->duration, (int) ($data['duration'] ?? 0)),
            'max_scroll' => max($visit->max_scroll, (int) ($data['max_scroll'] ?? 0)),
            'last_seen_at' => now(),
        ])->save();

        $rows = [];

        foreach ($data['events'] ?? [] as $event) {
            $rows[] = [
                'visit_id' => $visit->id,
                'type' => $event['type'],
                'target' => Str::limit($event['target'], 120, ''),
                'value' => (int) ($event['value'] ?? 0),
                'occurred_at' => now(),
            ];
        }

        if ($rows) {
            VisitEvent::insert($rows);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * One-way hash so repeat visitors can be counted without storing the IP.
     * Salted with the app key and the date, so hashes cannot be correlated
     * across days or lifted from a database dump.
     */
    protected function hashIp(?string $ip): ?string
    {
        if (blank($ip)) {
            return null;
        }

        return hash('sha256', $ip . config('app.key') . now()->toDateString());
    }

    protected function host(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        // Self-referrals are navigation, not traffic sources.
        return $host && $host !== $this->ownHost() ? Str::of($host)->lower()->ltrim('www.')->value() : null;
    }

    protected function ownHost(): ?string
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }

    protected function device(?string $agent): string
    {
        $agent = Str::lower((string) $agent);

        return match (true) {
            str_contains($agent, 'ipad') || str_contains($agent, 'tablet') => 'tablet',
            str_contains($agent, 'mobi') || str_contains($agent, 'android') => 'mobile',
            default => 'desktop',
        };
    }

    protected function browser(?string $agent): string
    {
        $agent = (string) $agent;

        // Order matters: Edge and Opera both claim to be Chrome.
        return match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'OPR/') => 'Opera',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Safari/') => 'Safari',
            default => 'Other',
        };
    }

    protected function platform(?string $agent): string
    {
        $agent = (string) $agent;

        return match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'Other',
        };
    }
}
