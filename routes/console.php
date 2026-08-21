<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keep analytics to a rolling 90-day window. Requires the scheduler to be
// running in production: * * * * * php artisan schedule:run
Schedule::command('analytics:prune')->weekly();
