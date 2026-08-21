<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * First-party analytics.
 *
 * Deliberately stores no directly identifying data: the IP is kept only as a
 * salted hash for rough uniqueness, and the session id is generated in the
 * browser per tab session rather than being a persistent tracking cookie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            // Random id minted client-side, scoped to one browser session.
            $table->string('session_id', 64)->unique();

            $table->string('ip_hash', 64)->nullable();
            $table->string('path')->default('/');
            // Long enough for the validation rule's 500 char ceiling.
            $table->string('referrer', 500)->nullable();
            $table->string('referrer_host')->nullable();
            $table->string('device', 20)->nullable();   // desktop | tablet | mobile
            $table->string('browser', 40)->nullable();
            $table->string('platform', 40)->nullable();
            $table->string('screen', 20)->nullable();

            // Seconds of active, tab-visible time. Updated as batches arrive.
            $table->unsignedInteger('duration')->default(0);
            $table->unsignedTinyInteger('max_scroll')->default(0);

            // Nullable: MySQL only grants the first TIMESTAMP column an
            // implicit default, and a second NOT NULL one is rejected under
            // NO_ZERO_DATE. Both are always written by the controller.
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('started_at');
            $table->index('referrer_host');
        });

        Schema::create('visit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();

            // section_time | click | scroll_depth
            $table->string('type', 20);

            // Section id, link label, or scroll milestone.
            $table->string('target');

            // Seconds for section_time, percent for scroll_depth.
            $table->unsignedInteger('value')->default(0);

            $table->timestamp('occurred_at')->nullable();

            $table->index(['type', 'target']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_events');
        Schema::dropIfExists('visits');
    }
};
