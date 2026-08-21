<?php

namespace App\Models;

/**
 * One line of the hero status strip: a label ("Currently") and a value
 * ("Rebuilding a billing pipeline").
 */
class StatusItem extends Sortable
{
    protected $table = 'status_items';

    protected $guarded = [];
}
