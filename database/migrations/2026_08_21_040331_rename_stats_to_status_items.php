<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * The hero strip used to hold vanity metrics ("40+ APIs shipped"). It now
 * carries current status ("Currently: rebuilding a billing pipeline"), so the
 * table name follows the meaning.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('stats', 'status_items');
    }

    public function down(): void
    {
        Schema::rename('status_items', 'stats');
    }
};
