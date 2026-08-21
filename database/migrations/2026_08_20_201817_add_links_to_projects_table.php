<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Splits the single project `url` into two: a live/demo link and a source
 * repository link. Either, both or neither may be set.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('live_url')->nullable()->after('summary');
            $table->string('repo_url')->nullable()->after('live_url');
        });

        // Whatever was in the old column was a live link in practice.
        DB::table('projects')->whereNotNull('url')->update([
            'live_url' => DB::raw('url'),
        ]);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('url')->nullable()->after('summary');
        });

        DB::table('projects')->whereNotNull('live_url')->update([
            'url' => DB::raw('live_url'),
        ]);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['live_url', 'repo_url']);
        });
    }
};
