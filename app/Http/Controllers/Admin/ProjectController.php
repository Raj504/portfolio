<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends AdminController
{
    public function index(): View
    {
        return view('admin.projects.index', ['projects' => Project::ordered()->get()]);
    }

    public function create(): View
    {
        return view('admin.projects.form', ['project' => new Project]);
    }

    public function store(Request $request): RedirectResponse
    {
        Project::create($this->validated($request));

        return $this->saved('admin.projects.index', 'Project created.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', ['project' => $project]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->validated($request));

        return $this->saved('admin.projects.index', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return $this->saved('admin.projects.index', 'Project deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'kind' => ['required', 'string', 'max:120'],
            'year' => ['required', 'string', 'max:20'],
            'summary' => ['required', 'string', 'max:1000'],
            'live_url' => ['nullable', 'url', 'max:255'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'stack' => ['nullable', 'string', 'max:500'],
            'metrics' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Both render as short chips, so they are entered comma-separated.
        $data['stack'] = $this->commas($request->input('stack'));
        $data['metrics'] = $this->commas($request->input('metrics'));
        $data['published'] = $request->boolean('published');
        $data['featured'] = $request->boolean('featured');

        return $data;
    }
}
