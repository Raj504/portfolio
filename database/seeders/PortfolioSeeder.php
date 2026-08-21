<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\Principle;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SkillGroup;
use App\Models\Social;
use App\Models\StatusItem;
use Illuminate\Database\Seeder;

/**
 * Imports config/portfolio.php into the database. That file is now the seed
 * data only -- once seeded, the admin panel is the source of truth.
 *
 * Safe to re-run: it clears the managed tables first.
 */
class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('portfolio');

        Profile::query()->delete();
        Profile::create([
            'name' => $config['name'],
            'role' => $config['role'],
            'tagline' => $config['tagline'],
            'blurb' => $config['blurb'],
            'location' => $config['location'],
            'email' => $config['email'],
            'phone' => $config['phone'] ?? null,
            'available' => $config['available'] ?? true,
            'availability_modes' => $config['availability_modes'] ?? [],
            'availability_note' => $config['availability_note'] ?? null,
        ]);

        Social::query()->delete();
        foreach ($config['socials'] as $i => $social) {
            Social::create($social + ['sort_order' => $i]);
        }

        StatusItem::query()->delete();
        foreach ($config['status'] as $i => $item) {
            StatusItem::create($item + ['sort_order' => $i]);
        }

        Principle::query()->delete();
        foreach ($config['principles'] as $i => $principle) {
            Principle::create($principle + ['sort_order' => $i]);
        }

        // Cascades to skills.
        SkillGroup::query()->delete();
        foreach ($config['skills'] as $i => $group) {
            $model = SkillGroup::create([
                'name' => $group['group'],
                'sort_order' => $i,
            ]);

            foreach ($group['items'] as $j => $item) {
                Skill::create([
                    'skill_group_id' => $model->id,
                    'name' => $item,
                    'sort_order' => $j,
                ]);
            }
        }

        Project::query()->delete();
        foreach ($config['projects'] as $i => $project) {
            Project::create([
                'title' => $project['title'],
                'kind' => $project['kind'],
                'year' => $project['year'],
                'summary' => $project['summary'],
                'live_url' => $project['live_url'] ?? null,
                'repo_url' => $project['repo_url'] ?? null,
                'stack' => $project['stack'],
                'metrics' => $project['metrics'],
                'published' => true,
                'sort_order' => $i,
            ]);
        }

        Experience::query()->delete();
        foreach ($config['experience'] as $i => $job) {
            Experience::create([
                'role' => $job['role'],
                'company' => $job['company'],
                'period' => $job['period'],
                'points' => $job['points'],
                'sort_order' => $i,
            ]);
        }
    }
}
