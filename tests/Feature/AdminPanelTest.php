<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\SkillGroup;
use App\Models\User;
use App\Support\SiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('correct-horse-battery-staple'),
        ]);

        Profile::create([
            'name' => 'Test Person',
            'role' => 'Backend Engineer',
            'tagline' => 'Tagline',
            'blurb' => 'Blurb',
            'location' => 'India',
            'email' => 'test@local',
            'phone' => '+91 12345 67890',
            'available' => true,
            'availability_modes' => ['Remote', 'Hybrid'],
            'availability_note' => 'Anywhere.',
        ]);
    }

    /* ---------------------------------------------------------------- auth */

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        foreach (['/admin', '/admin/projects', '/admin/messages', '/admin/profile'] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
    }

    public function test_guests_cannot_write(): void
    {
        $this->post(route('admin.projects.store'), ['title' => 'Sneaky'])
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_login_rejects_a_bad_password(): void
    {
        $this->post(route('admin.login.store'), [
            'email' => 'admin@test.local',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_succeeds_with_correct_credentials(): void
    {
        $this->post(route('admin.login.store'), [
            'email' => 'admin@test.local',
            'password' => 'correct-horse-battery-staple',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_logout_ends_the_session(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    /* --------------------------------------------------------------- pages */

    public function test_every_admin_page_loads(): void
    {
        $this->seedContent();

        $urls = [
            '/admin',
            '/admin/profile',
            '/admin/projects',
            '/admin/projects/create',
            '/admin/experiences',
            '/admin/experiences/create',
            '/admin/skills',
            '/admin/skills/create',
            '/admin/principles',
            '/admin/principles/create',
            '/admin/stats',
            '/admin/stats/create',
            '/admin/socials',
            '/admin/socials/create',
            '/admin/messages',
        ];

        foreach ($urls as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_edit_pages_load(): void
    {
        $this->seedContent();

        $this->actingAs($this->admin)
            ->get(route('admin.projects.edit', Project::first()))
            ->assertOk()
            ->assertSee('Ledger');

        $this->actingAs($this->admin)
            ->get(route('admin.skills.edit', SkillGroup::first()))
            ->assertOk();
    }

    /* ---------------------------------------------------------------- crud */

    public function test_a_project_can_be_created_and_appears_on_the_site(): void
    {
        $this->seedContent();

        // Warm the public cache first, otherwise this proves nothing about
        // invalidation -- an empty cache would pass either way.
        $this->get('/')->assertOk()->assertDontSee('Brand New Service');

        $this->actingAs($this->admin)->post(route('admin.projects.store'), [
            'title' => 'Brand New Service',
            'kind' => 'Queue worker',
            'year' => '2026',
            'summary' => 'A service created through the admin panel during a test run.',
            'stack' => 'Laravel, Redis',
            'metrics' => '1M jobs, p99 20ms',
            'live_url' => 'https://demo.example.com',
            'repo_url' => 'https://github.com/example/repo',
            'published' => '1',
        ])->assertRedirect(route('admin.projects.index'));

        $project = Project::firstWhere('title', 'Brand New Service');

        // Comma-separated input is stored as a clean array.
        $this->assertSame(['Laravel', 'Redis'], $project->stack);
        $this->assertSame(['1M jobs', 'p99 20ms'], $project->metrics);
        $this->assertTrue($project->published);

        $this->assertSame('https://demo.example.com', $project->live_url);
        $this->assertSame('https://github.com/example/repo', $project->repo_url);

        // The public page must reflect it immediately -- i.e. the cache flushed.
        $this->get('/')
            ->assertOk()
            ->assertSee('Brand New Service')
            ->assertSee('https://demo.example.com')
            ->assertSee('https://github.com/example/repo');
    }

    public function test_project_links_are_optional_and_render_independently(): void
    {
        // Live link only.
        $this->actingAs($this->admin)->post(route('admin.projects.store'), [
            'title' => 'Live Only',
            'kind' => 'Demo',
            'year' => '2026',
            'summary' => 'This project has a deployed site but no public repository.',
            'live_url' => 'https://live-only.example.com',
            'published' => '1',
        ]);

        // Repo link only.
        $this->actingAs($this->admin)->post(route('admin.projects.store'), [
            'title' => 'Repo Only',
            'kind' => 'Library',
            'year' => '2026',
            'summary' => 'This project is open source but has nothing deployed anywhere.',
            'repo_url' => 'https://github.com/example/repo-only',
            'published' => '1',
        ]);

        // Neither.
        $this->actingAs($this->admin)->post(route('admin.projects.store'), [
            'title' => 'No Links',
            'kind' => 'Internal',
            'year' => '2026',
            'summary' => 'A closed-source internal service with no public presence at all.',
            'published' => '1',
        ]);

        $this->assertNull(Project::firstWhere('title', 'Live Only')->repo_url);
        $this->assertNull(Project::firstWhere('title', 'Repo Only')->live_url);

        $response = $this->get('/')->assertOk();
        $response->assertSee('https://live-only.example.com');
        $response->assertSee('https://github.com/example/repo-only');

        // The card with no links must not render an empty link row.
        $this->assertSame(1, substr_count($response->getContent(), 'Live site'));
        $this->assertSame(1, substr_count($response->getContent(), 'Source code'));
    }

    public function test_project_links_must_be_valid_urls(): void
    {
        $this->actingAs($this->admin)->post(route('admin.projects.store'), [
            'title' => 'Bad Links',
            'kind' => 'Thing',
            'year' => '2026',
            'summary' => 'A project submitted with two links that are not real URLs.',
            'live_url' => 'not-a-url',
            'repo_url' => 'also not a url',
        ])->assertSessionHasErrors(['live_url', 'repo_url']);

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_an_unpublished_project_is_hidden_from_the_site(): void
    {
        $this->actingAs($this->admin)->post(route('admin.projects.store'), [
            'title' => 'Secret Service',
            'kind' => 'Internal',
            'year' => '2026',
            'summary' => 'This one should not be visible on the public page at all.',
            'published' => '0',
        ]);

        $this->get('/')->assertOk()->assertDontSee('Secret Service');
    }

    public function test_a_project_can_be_updated_and_deleted(): void
    {
        $this->seedContent();
        $project = Project::first();

        $this->actingAs($this->admin)->put(route('admin.projects.update', $project), [
            'title' => 'Renamed Project',
            'kind' => $project->kind,
            'year' => $project->year,
            'summary' => $project->summary,
            'published' => '1',
        ])->assertRedirect(route('admin.projects.index'));

        $this->assertSame('Renamed Project', $project->fresh()->title);

        $this->actingAs($this->admin)
            ->delete(route('admin.projects.destroy', $project))
            ->assertRedirect(route('admin.projects.index'));

        $this->assertModelMissing($project);
    }

    public function test_project_validation_rejects_empty_input(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.projects.store'), [])
            ->assertSessionHasErrors(['title', 'kind', 'year', 'summary']);

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_experience_points_are_split_by_line(): void
    {
        $this->actingAs($this->admin)->post(route('admin.experiences.store'), [
            'role' => 'Staff Engineer',
            'company' => 'Somewhere',
            'period' => '2026 - Present',
            'points' => "First point\nSecond point\n\n  Third point  ",
        ])->assertRedirect(route('admin.experiences.index'));

        $this->assertSame(
            ['First point', 'Second point', 'Third point'],
            Experience::firstWhere('role', 'Staff Engineer')->points
        );
    }

    public function test_saving_a_skill_group_replaces_its_skills(): void
    {
        $this->actingAs($this->admin)->post(route('admin.skills.store'), [
            'name' => 'Testing',
            'skills' => 'PHPUnit, Pest, Dusk',
        ]);

        $group = SkillGroup::firstWhere('name', 'Testing');
        $this->assertSame(['PHPUnit', 'Pest', 'Dusk'], $group->skills->pluck('name')->all());

        $this->actingAs($this->admin)->put(route('admin.skills.update', $group), [
            'name' => 'Testing',
            'skills' => 'Pest',
        ]);

        $this->assertSame(['Pest'], $group->fresh()->skills->pluck('name')->all());
    }

    public function test_deleting_a_skill_group_removes_its_skills(): void
    {
        $this->actingAs($this->admin)->post(route('admin.skills.store'), [
            'name' => 'Temp',
            'skills' => 'One, Two',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.skills.destroy', SkillGroup::firstWhere('name', 'Temp')));

        $this->assertDatabaseCount('skills', 0);
    }

    /* ------------------------------------------------------- work section */

    public function test_a_featured_project_renders_as_the_hero_card(): void
    {
        $this->makeProjects(4);
        Project::firstWhere('title', 'Project 3')->update(['featured' => true]);
        SiteContent::flush();

        $html = $this->get('/')->assertOk()->getContent();

        // Exactly one hero card, and it is the one that was flagged.
        $this->assertSame(1, substr_count($html, 'Featured'));
        $this->assertLessThan(strpos($html, 'id="project-grid"'), strpos($html, 'Project 3'));
    }

    public function test_the_first_project_is_promoted_when_none_is_featured(): void
    {
        $this->makeProjects(5);
        SiteContent::flush();

        $html = $this->get('/')->assertOk()->getContent();
        $gridStart = strpos($html, 'id="project-grid"');

        $this->assertLessThan($gridStart, strpos($html, 'Project 1'));
        $this->assertGreaterThan($gridStart, strpos($html, 'Project 2'));
    }

    public function test_two_projects_are_not_promoted_to_a_hero_card(): void
    {
        // Promoting one of two would leave a lone card in the grid.
        $this->makeProjects(2);
        SiteContent::flush();

        $this->get('/')->assertOk()->assertDontSee('Featured');
    }

    public function test_a_short_list_is_not_collapsed(): void
    {
        $this->makeProjects(4);
        SiteContent::flush();

        $this->get('/')
            ->assertOk()
            ->assertDontSee('show-more-projects')
            ->assertDontSee('data-project-extra', false);
    }

    public function test_a_long_list_collapses_past_six_grid_cards(): void
    {
        $this->makeProjects(10);
        SiteContent::flush();

        $html = $this->get('/')->assertOk()->getContent();

        // One hero plus nine grid cards: six shown, three collapsed.
        $this->assertSame(3, substr_count($html, 'data-project-extra'));
        $this->assertStringContainsString('Show all 10 projects', $html);

        // Collapsed cards stay in the HTML so crawlers still see them.
        $this->assertStringContainsString('Project 10', $html);
    }

    public function test_unpublished_projects_are_excluded_from_the_count(): void
    {
        $this->makeProjects(4);
        Project::firstWhere('title', 'Project 4')->update(['published' => false]);
        SiteContent::flush();

        $this->get('/')->assertOk()->assertDontSee('Project 4');
    }

    /* ------------------------------------------------------------- profile */

    public function test_profile_updates_flow_through_to_the_public_page(): void
    {
        $this->actingAs($this->admin)->put(route('admin.profile.update'), [
            'name' => 'Updated Name',
            'role' => 'Platform Engineer',
            'tagline' => 'New tagline',
            'blurb' => 'A new blurb for the hero section.',
            'location' => 'Bengaluru',
            'email' => 'new@example.com',
            'phone' => '+91 98765 43210',
            'available' => '1',
            'availability_modes' => ['Remote', 'On-site'],
            'availability_note' => 'Open to anything.',
        ])->assertRedirect(route('admin.profile.edit'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Updated Name')
            ->assertSee('+91 98765 43210')
            ->assertSee('Open to anything.')
            ->assertSee('On-site');
    }

    public function test_unchecking_availability_hides_the_badge(): void
    {
        $this->actingAs($this->admin)->put(route('admin.profile.update'), [
            'name' => 'Test Person',
            'role' => 'Backend Engineer',
            'tagline' => 'Tagline',
            'blurb' => 'Blurb',
            'location' => 'India',
            'email' => 'test@local',
            'available' => '0',
        ]);

        $this->get('/')->assertOk()->assertSee('Not currently available');
    }

    /* ------------------------------------------------------------ messages */

    public function test_a_contact_submission_is_stored_and_readable_in_the_admin(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Work enquiry',
            'message' => 'I would like to talk about a backend project we are planning.',
        ]);

        $message = ContactMessage::firstWhere('email', 'jane@example.com');
        $this->assertNotNull($message);
        $this->assertTrue($message->isUnread());

        // Opening it marks it read.
        $this->actingAs($this->admin)
            ->get(route('admin.messages.show', $message))
            ->assertOk()
            ->assertSee('Work enquiry');

        $this->assertFalse($message->fresh()->isUnread());
    }

    public function test_the_honeypot_silently_drops_bot_submissions(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'Spam',
            'message' => 'Buy things from this website right now please.',
            'website' => 'http://spam.example',
        ])->assertRedirect();

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_a_message_can_be_deleted(): void
    {
        $message = ContactMessage::create([
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'subject' => 'Hi',
            'message' => 'Hello there, this is a message body.',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.messages.destroy', $message))
            ->assertRedirect(route('admin.messages.index'));

        $this->assertModelMissing($message);
    }

    /* ------------------------------------------------------------- helpers */

    /**
     * Create N published projects named "Project 1".."Project N".
     */
    protected function makeProjects(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            Project::create([
                'title' => "Project {$i}",
                'kind' => 'Service',
                'year' => '2025',
                'summary' => "Summary for project number {$i}, long enough to read like real copy.",
                'stack' => ['Laravel'],
                'metrics' => ['1M req'],
                'published' => true,
                'sort_order' => $i,
            ]);
        }
    }

    protected function seedContent(): void
    {
        Project::create([
            'title' => 'Ledger Core',
            'kind' => 'Ledger',
            'year' => '2025',
            'summary' => 'A ledger service used as fixture data for these tests.',
            'stack' => ['Laravel'],
            'metrics' => ['12M entries'],
        ]);

        Experience::create([
            'role' => 'Backend Engineer',
            'company' => 'Somewhere',
            'period' => '2024 - Present',
            'points' => ['Did a thing.'],
        ]);

        $group = SkillGroup::create(['name' => 'Languages']);
        $group->skills()->create(['name' => 'PHP', 'sort_order' => 0]);
    }
}
