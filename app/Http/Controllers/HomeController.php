<?php

namespace App\Http\Controllers;

use App\Support\SiteContent;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(SiteContent $site): View
    {
        // Passed to the root view so the layout and every included partial
        // can read it without a separate composer.
        return view('home', ['site' => $site]);
    }
}
