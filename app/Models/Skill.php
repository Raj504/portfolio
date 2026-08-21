<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Skill extends Sortable
{
    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SkillGroup::class, 'skill_group_id');
    }
}
