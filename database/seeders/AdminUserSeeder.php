<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates the single admin account.
 *
 * Set ADMIN_EMAIL and ADMIN_PASSWORD in .env before seeding. If no password is
 * set we generate a strong random one and print it once -- deliberately, so
 * there is never a default password baked into the repository.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // config(), never env(): env() is null under a cached config, which
        // is the normal production state.
        $email = config('portfolio.admin_email') ?: 'admin@example.com';

        if (User::where('email', $email)->exists()) {
            $this->command?->warn("Admin user {$email} already exists, leaving its password alone.");
            $this->command?->warn('Use `php artisan admin:password` to change it.');

            return;
        }

        $password = config('portfolio.admin_password');
        $generated = false;

        if (blank($password)) {
            $password = Str::password(20);
            $generated = true;
        }

        User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->command?->info("Admin user created: {$email}");

        if ($generated) {
            $this->command?->warn("Generated password: {$password}");
            $this->command?->warn('Copy it now. It is not stored anywhere and will not be shown again.');
        }
    }
}
