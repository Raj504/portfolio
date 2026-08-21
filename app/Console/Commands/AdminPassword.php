<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Create or update the admin login.
 *
 * The production-safe way to set credentials: the password is typed into a
 * hidden prompt, so it never lands in .env, in shell history, or in a
 * deployment log. This is the tool to reach for after the first deploy --
 * AdminUserSeeder only ever runs on a fresh install.
 */
class AdminPassword extends Command
{
    protected $signature = 'admin:password
                            {--email= : Email for the admin account}';

    protected $description = 'Set the admin email and password (prompts securely)';

    public function handle(): int
    {
        $existing = User::query()->first();

        $email = $this->option('email')
            ?: $this->ask('Admin email', $existing?->email ?: config('portfolio.admin_email'));

        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->error('That is not a valid email address.');

            return self::FAILURE;
        }

        // secret() hides the input; nothing is echoed to the terminal.
        $password = $this->secret('New password (input hidden)');
        $confirm = $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('The passwords do not match. Nothing was changed.');

            return self::FAILURE;
        }

        if (strlen((string) $password) < 12) {
            $this->error('Use at least 12 characters. Nothing was changed.');

            return self::FAILURE;
        }

        if ($existing) {
            // Update in place rather than inserting: this app has exactly one
            // admin, and creating a second would leave the old login working.
            $existing->forceFill([
                'email' => $email,
                'password' => Hash::make($password),
            ])->save();

            $this->info("Updated the admin account: {$email}");
        } else {
            User::create([
                'name' => 'Admin',
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->info("Created the admin account: {$email}");
        }

        $this->line('Existing browser sessions stay signed in until they expire.');

        return self::SUCCESS;
    }
}
