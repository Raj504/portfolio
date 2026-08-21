<?php

namespace App\Http\Controllers\Admin;

use App\Models\Experience;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExperienceController extends AdminController
{
    public function index(): View
    {
        return view('admin.experiences.index', ['experiences' => Experience::ordered()->get()]);
    }

    public function create(): View
    {
        return view('admin.experiences.form', ['experience' => new Experience]);
    }

    public function store(Request $request): RedirectResponse
    {
        Experience::create($this->validated($request));

        return $this->saved('admin.experiences.index', 'Role created.');
    }

    public function edit(Experience $experience): View
    {
        return view('admin.experiences.form', ['experience' => $experience]);
    }

    public function update(Request $request, Experience $experience): RedirectResponse
    {
        $experience->update($this->validated($request));

        return $this->saved('admin.experiences.index', 'Role updated.');
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $experience->delete();

        return $this->saved('admin.experiences.index', 'Role deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:120'],
            'period' => ['required', 'string', 'max:60'],
            'points' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Bullet points are long enough to deserve one line each.
        $data['points'] = $this->lines($request->input('points'));

        return $data;
    }
}
