<?php

namespace App\Http\Controllers\Admin;

use App\Models\Stat;

class StatController extends SimpleResourceController
{
    protected string $model = Stat::class;
    protected string $route = 'admin.stats';
    protected string $label = 'Stat';

    protected function rules(): array
    {
        return [
            'value' => ['required', 'string', 'max:30'],
            'label' => ['required', 'string', 'max:80'],
        ];
    }
}
