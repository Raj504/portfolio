<?php

namespace App\Http\Controllers\Admin;

use App\Models\Profile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends AdminController
{
    public function edit(): View
    {
        return view('admin.profile', ['profile' => Profile::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'max:100'],
            'tagline' => ['required', 'string', 'max:180'],
            'blurb' => ['required', 'string', 'max:1000'],
            'location' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'availability_note' => ['nullable', 'string', 'max:200'],
            'availability_modes' => ['nullable', 'array'],
            'availability_modes.*' => ['string', 'max:60'],
        ]);

        $data['available'] = $request->boolean('available');
        $data['availability_modes'] = $data['availability_modes'] ?? [];

        // There is only ever one row; create it if the seeder never ran.
        $profile = Profile::query()->first();
        $profile ? $profile->update($data) : Profile::create($data);

        return $this->saved('admin.profile.edit', 'Profile updated.');
    }
}
