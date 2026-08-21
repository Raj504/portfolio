<?php

namespace App\Http\Controllers\Admin;

use App\Models\Social;

class SocialController extends SimpleResourceController
{
    protected string $model = Social::class;

    protected string $route = 'admin.socials';

    protected string $label = 'Link';

    protected function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:60'],
            'url' => ['required', 'url', 'max:255'],
        ];
    }
}
