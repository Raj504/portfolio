<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Base for the admin-managed lists. They all share the same need: a manual
 * display order, with the newest item landing at the end by default.
 */
abstract class Sortable extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if ($model->sort_order === null) {
                $model->sort_order = (static::max('sort_order') ?? -1) + 1;
            }
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
