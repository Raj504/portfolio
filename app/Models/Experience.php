<?php

namespace App\Models;

class Experience extends Sortable
{
    protected $guarded = [];

    protected $casts = [
        'points' => 'array',
    ];
}
