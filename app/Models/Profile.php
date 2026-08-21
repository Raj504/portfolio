<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'available' => 'boolean',
        'availability_modes' => 'array',
    ];

    /**
     * The site has exactly one profile. Fall back to an empty model so a
     * fresh install renders instead of blowing up on null.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static;
    }
}
