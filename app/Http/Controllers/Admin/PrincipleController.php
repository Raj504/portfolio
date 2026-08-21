<?php

namespace App\Http\Controllers\Admin;

use App\Models\Principle;

class PrincipleController extends SimpleResourceController
{
    protected string $model = Principle::class;
    protected string $route = 'admin.principles';
    protected string $label = 'Principle';

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:80'],
            'body' => ['required', 'string', 'max:400'],
        ];
    }
}
