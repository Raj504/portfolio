<?php

namespace App\Http\Controllers\Admin;

use App\Models\Skill;
use App\Models\SkillGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SkillGroupController extends AdminController
{
    public function index(): View
    {
        return view('admin.skills.index', [
            'groups' => SkillGroup::with('skills')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.skills.form', ['group' => new SkillGroup]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $group = SkillGroup::create($data['group']);
        $this->syncSkills($group, $data['skills']);

        return $this->saved('admin.skills.index', 'Skill group created.');
    }

    public function edit(SkillGroup $skill): View
    {
        return view('admin.skills.form', ['group' => $skill->load('skills')]);
    }

    public function update(Request $request, SkillGroup $skill): RedirectResponse
    {
        $data = $this->validated($request);
        $skill->update($data['group']);
        $this->syncSkills($skill, $data['skills']);

        return $this->saved('admin.skills.index', 'Skill group updated.');
    }

    public function destroy(SkillGroup $skill): RedirectResponse
    {
        $skill->delete();

        return $this->saved('admin.skills.index', 'Skill group deleted.');
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'skills' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        return [
            'group' => [
                'name' => $validated['name'],
                'sort_order' => $validated['sort_order'] ?? 0,
            ],
            'skills' => $this->commas($request->input('skills')),
        ];
    }

    /**
     * Replace the group's skills wholesale. The list is short and order
     * matters, so rewriting it is simpler than diffing.
     */
    protected function syncSkills(SkillGroup $group, array $names): void
    {
        $group->skills()->delete();

        foreach ($names as $i => $name) {
            Skill::create([
                'skill_group_id' => $group->id,
                'name' => $name,
                'sort_order' => $i,
            ]);
        }
    }
}
