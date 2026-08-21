<?php

namespace Tests\Feature;

use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_renders_seeded_content(): void
    {
        $this->seed(PortfolioSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee(config('portfolio.name'))
            ->assertSee('Ledger Core')          // a project
            ->assertSee('Boring on purpose')    // a principle
            ->assertSee('Senior Backend Engineer'); // an experience
    }

    public function test_the_homepage_renders_before_anything_is_seeded(): void
    {
        // A fresh install has no profile row. The page should still respond
        // rather than fataling on a null model.
        $this->get('/')->assertOk();
    }

    public function test_contact_validation_rejects_a_short_message(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'subject' => 'Hi',
            'message' => 'too short',
        ])->assertSessionHasErrors('message');

        $this->assertDatabaseCount('contact_messages', 0);
    }
}
