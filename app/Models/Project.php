<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Project extends Sortable
{
    protected $guarded = [];

    protected $casts = [
        'stack' => 'array',
        'metrics' => 'array',
        'published' => 'boolean',
        'featured' => 'boolean',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
