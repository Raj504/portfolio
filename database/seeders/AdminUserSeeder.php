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
        $email = config('portfolio.admin_email') ?: env('ADMIN_EMAIL');
        $email = $email ?: 'admin@example.com';

        if (User::where('email', $email)->exists()) {
            $this->command?->warn("Admin user {$email} already exists, leaving its password alone.");

            return;
        }

        $password = env('ADMIN_PASSWORD');
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
