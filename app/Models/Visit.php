<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(VisitEvent::class);
    }

    public function scopeSince(Builder $query, $date): Builder
    {
        return $query->where('started_at', '>=', $date);
    }

    /**
     * A visit that recorded no time and no scrolling is almost always a bot
     * or an instant bounce; it would skew the averages.
     */
    public function scopeEngaged(Builder $query): Builder
    {
        return $query->where('duration', '>', 0);
    }
}
