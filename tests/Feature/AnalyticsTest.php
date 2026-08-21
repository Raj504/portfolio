<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visit;
use App\Models\VisitEvent;
use App\Support\Analytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'sid' => str_repeat('a', 32),
            'path' => '/',
            'referrer' => 'https://news.ycombinator.com/item?id=1',
            'screen' => '1920x1080',
            'duration' => 45,
            'max_scroll' => 80,
            'events' => [
                ['type' => 'section_time', 'target' => 'work', 'value' => 30],
                ['type' => 'section_time', 'target' => 'about', 'value' => 15],
                ['type' => 'click', 'target' => 'project:Ledger Core:live', 'value' => 0],
                ['type' => 'scroll_depth', 'target' => '75', 'value' => 75],
            ],
        ], $overrides);
    }

    /* ------------------------------------------------------------- ingest */

    public function test_a_beacon_creates_a_visit_and_its_events(): void
    {
        $this->postJson(route('track'), $this->payload())->assertOk();

        $visit = Visit::firstWhere('session_id', str_repeat('a', 32));

        $this->assertNotNull($visit);
        $this->assertSame(45, $visit->duration);
        $this->assertSame(80, $visit->max_scroll);
        $this->assertSame('news.ycombinator.com', $visit->referrer_host);
        $this->assertCount(4, $visit->events);
    }

    public function test_repeat_beacons_extend_the_same_visit(): void
    {
        $this->postJson(route('track'), $this->payload())->assertOk();

        // Second batch: more time, deeper scroll, one more click.
        $this->postJson(route('track'), $this->payload([
            'duration' => 120,
            'max_scroll' => 100,
            'events' => [
                ['type' => 'section_time', 'target' => 'work', 'value' => 20],
                ['type' => 'click', 'target' => 'contact:email', 'value' => 0],
            ],
        ]))->assertOk();

        $this->assertSame(1, Visit::count());

        $visit = Visit::first();
        $this->assertSame(120, $visit->duration);
        $this->assertSame(100, $visit->max_scroll);
        $this->assertCount(6, $visit->events);

        // Section time is additive across batches.
        $workSeconds = VisitEvent::where('type', 'section_time')->where('target', 'work')->sum('value');
        $this->assertSame(50, (int) $workSeconds);
    }

    public function test_duration_never_goes_backwards(): void
    {
        $this->postJson(route('track'), $this->payload(['duration' => 300, 'max_scroll' => 90]));

        // A late, stale beacon must not shrink the recorded totals.
        $this->postJson(route('track'), $this->payload(['duration' => 10, 'max_scroll' => 5, 'events' => []]));

        $visit = Visit::first();
        $this->assertSame(300, $visit->duration);
        $this->assertSame(90, $visit->max_scroll);
    }

    /* ------------------------------------------------------------ privacy */

    public function test_the_raw_ip_is_never_stored(): void
    {
        $this->postJson(route('track'), $this->payload());

        $visit = Visit::first();

        $this->assertNotNull($visit->ip_hash);
        $this->assertSame(64, strlen($visit->ip_hash));
        $this->assertStringNotContainsString('127.0.0.1', $visit->ip_hash);
    }

    public function test_do_not_track_is_honoured(): void
    {
        $this->withHeader('DNT', '1')
            ->postJson(route('track'), $this->payload())
            ->assertOk()
            ->assertJsonPath('ignored', 'dnt');

        $this->assertSame(0, Visit::count());
    }

    public function test_global_privacy_control_is_honoured(): void
    {
        $this->withHeader('Sec-GPC', '1')
            ->postJson(route('track'), $this->payload())
            ->assertOk();

        $this->assertSame(0, Visit::count());
    }

    public function test_the_signed_in_owner_never_boots_the_tracker(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('secret-password-here'),
        ]);

        // Owner exclusion moved into the page: /track is stateless now, so the
        // guard has to be that no beacon is ever sent in the first place.
        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('<meta name="analytics" content="off">', false);
    }

    public function test_a_guest_does_boot_the_tracker(): void
    {
        // Separate test on purpose: actingAs() stays in effect for the rest of
        // the test it is called in, so a guest request must start clean.
        $this->get('/')
            ->assertOk()
            ->assertDontSee('<meta name="analytics" content="off">', false);
    }

    public function test_beacons_from_another_site_are_rejected(): void
    {
        $this->withHeader('Origin', 'https://evil.example')
            ->postJson(route('track'), $this->payload())
            ->assertStatus(403);

        $this->assertSame(0, Visit::count());
    }

    public function test_beacons_from_this_site_are_accepted(): void
    {
        $this->withHeader('Origin', 'http://localhost')
            ->postJson(route('track'), $this->payload())
            ->assertOk();

        $this->assertSame(1, Visit::count());
    }

    public function test_the_track_route_does_not_touch_the_session(): void
    {
        // Sessions cost queries and, worse, lock the session row -- which made
        // concurrent beacons queue up behind each other.
        $middleware = collect(app('router')->getRoutes()->getByName('track')->gatherMiddleware())
            ->map(fn ($m) => is_string($m) ? $m : '')
            ->implode(',');

        $this->assertStringNotContainsString('StartSession', $middleware);
        $this->assertStringNotContainsString('ValidateCsrfToken', $middleware);
    }

    public function test_self_referrals_are_not_counted_as_traffic_sources(): void
    {
        config(['app.url' => 'https://myportfolio.test']);

        $this->postJson(route('track'), $this->payload([
            'referrer' => 'https://myportfolio.test/some-page',
        ]));

        $this->assertNull(Visit::first()->referrer_host);
    }

    /* --------------------------------------------------------- validation */

    public function test_unknown_event_types_are_rejected(): void
    {
        $this->postJson(route('track'), $this->payload([
            'events' => [['type' => 'keylog', 'target' => 'password', 'value' => 1]],
        ]))->assertStatus(422);

        $this->assertSame(0, Visit::count());
    }

    public function test_absurd_durations_are_rejected(): void
    {
        $this->postJson(route('track'), $this->payload(['duration' => 999999]))
            ->assertStatus(422);
    }

    public function test_a_malformed_session_id_is_rejected(): void
    {
        $this->postJson(route('track'), $this->payload(['sid' => 'short']))
            ->assertStatus(422);
    }

    /* ---------------------------------------------------------- reporting */

    public function test_the_dashboard_aggregates_what_was_collected(): void
    {
        $this->postJson(route('track'), $this->payload());

        $this->postJson(route('track'), $this->payload([
            'sid' => str_repeat('b', 32),
            'referrer' => null,
            'duration' => 90,
            'max_scroll' => 100,
            'events' => [
                ['type' => 'section_time', 'target' => 'work', 'value' => 60],
                ['type' => 'click', 'target' => 'project:Ledger Core:live', 'value' => 0],
            ],
        ]));

        $analytics = new Analytics(now()->subDays(30)->startOfDay());

        $this->assertSame(2, $analytics->summary()['Visits']);

        // Work is the most-read section: 30 + 60 seconds across two visits.
        $sections = $analytics->sectionTime();
        $this->assertSame('Work', $sections->first()['label']);
        $this->assertSame(90, $sections->first()['seconds']);
        $this->assertSame(2, $sections->first()['visits']);

        // The live link was clicked by both visitors.
        $project = $analytics->projectClicks()->first();
        $this->assertSame('Ledger Core', $project['title']);
        $this->assertSame('Live site', $project['kind']);
        $this->assertSame(2, $project['total']);

        // Both reached 75%, only one reached 100%.
        $funnel = $analytics->scrollFunnel()->keyBy('label');
        $this->assertSame(2, $funnel['75%']['visits']);
        $this->assertSame(1, $funnel['100%']['visits']);

        // One referred visit, one direct.
        $referrers = $analytics->referrers()->keyBy('label');
        $this->assertSame(1, $referrers['news.ycombinator.com']['total']);
        $this->assertSame(1, $referrers['Direct']['total']);
    }

    public function test_the_analytics_page_loads_for_the_admin_only(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('secret-password-here'),
        ]);

        $this->get(route('admin.analytics'))->assertRedirect(route('admin.login'));

        $this->actingAs($admin)->get(route('admin.analytics'))->assertOk();

        foreach ([7, 30, 90] as $days) {
            $this->actingAs($admin)
                ->get(route('admin.analytics', ['days' => $days]))
                ->assertOk();
        }

        // An out-of-range window falls back rather than erroring.
        $this->actingAs($admin)->get(route('admin.analytics', ['days' => 9999]))->assertOk();
    }

    /* ---------------------------------------------------------- retention */

    public function test_pruning_removes_old_visits_and_their_events(): void
    {
        $this->postJson(route('track'), $this->payload());

        Visit::first()->forceFill(['started_at' => now()->subDays(120)])->save();

        $this->artisan('analytics:prune')->assertSuccessful();

        $this->assertSame(0, Visit::count());
        $this->assertSame(0, VisitEvent::count());
    }

    public function test_pruning_keeps_recent_visits(): void
    {
        $this->postJson(route('track'), $this->payload());

        $this->artisan('analytics:prune')->assertSuccessful();

        $this->assertSame(1, Visit::count());
    }
}
