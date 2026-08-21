<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillGroup extends Sortable
{
    protected $guarded = [];

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class)->orderBy('sort_order');
    }
}
