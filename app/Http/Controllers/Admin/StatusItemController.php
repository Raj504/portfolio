<?php

namespace App\Http\Controllers\Admin;

use App\Models\StatusItem;

class StatusItemController extends SimpleResourceController
{
    protected string $model = StatusItem::class;
    protected string $route = 'admin.status';
    protected string $label = 'Status item';

    protected function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:40'],
            'value' => ['required', 'string', 'max:80'],
        ];
    }
}
