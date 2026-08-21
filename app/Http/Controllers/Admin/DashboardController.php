<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Contracts\View\View;

class DashboardController extends AdminController
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'Projects' => Project::count(),
                'Roles' => Experience::count(),
                'Skills' => Skill::count(),
                'Unread messages' => ContactMessage::unread()->count(),
            ],
            'recent' => ContactMessage::latest()->limit(5)->get(),
        ]);
    }
}
