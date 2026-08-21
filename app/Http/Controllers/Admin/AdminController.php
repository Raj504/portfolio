<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;

abstract class AdminController extends Controller
{
    /**
     * Finish a write: drop the public cache and bounce back with a message.
     * Every admin write goes through here so no path can forget the flush.
     */
    protected function saved(string $route, string $message): RedirectResponse
    {
        SiteContent::flush();

        return redirect()->route($route)->with('status', $message);
    }

    /**
     * Turn a textarea of one-item-per-line into a clean array.
     */
    protected function lines(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Turn a comma-separated field into a clean array.
     */
    protected function commas(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
