<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sortable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Socials, stats and principles are all the same shape: a short ordered list
 * of two or three plain text fields. They share one controller body and differ
 * only in the properties below.
 */
abstract class SimpleResourceController extends AdminController
{
    /** @var class-string<Sortable> */
    protected string $model;

    /** Route name prefix, e.g. "admin.socials". */
    protected string $route;

    /** Singular noun used in flash messages, e.g. "Link". */
    protected string $label;

    /** Field name => validation rules. */
    abstract protected function rules(): array;

    public function index(): View
    {
        return view('admin.simple.index', [
            'items' => $this->model::ordered()->get(),
            'fields' => array_keys($this->rules()),
            'route' => $this->route,
            'label' => $this->label,
        ]);
    }

    public function create(): View
    {
        return $this->form(new $this->model());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->model::create($this->validated($request));

        return $this->saved("{$this->route}.index", "{$this->label} created.");
    }

    public function edit(int $id): View
    {
        return $this->form($this->model::findOrFail($id));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->model::findOrFail($id)->update($this->validated($request));

        return $this->saved("{$this->route}.index", "{$this->label} updated.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->model::findOrFail($id)->delete();

        return $this->saved("{$this->route}.index", "{$this->label} deleted.");
    }

    protected function form(Sortable $item): View
    {
        return view('admin.simple.form', [
            'item' => $item,
            'fields' => array_keys($this->rules()),
            'route' => $this->route,
            'label' => $this->label,
        ]);
    }

    protected function validated(Request $request): array
    {
        return $request->validate($this->rules() + [
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
