<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:user {email : Login email} {--name=Admin : Display name}';

    protected $description = 'Create the admin login, or reset its password if the email already exists';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (Validator::make(['email' => $email], ['email' => 'email'])->fails()) {
            $this->error('That is not a valid email address.');

            return self::FAILURE;
        }

        $password = $this->secret('Password (min. 10 characters)');
        if (strlen((string) $password) < 10) {
            $this->error('Password must be at least 10 characters.');

            return self::FAILURE;
        }
        if ($password !== $this->secret('Repeat password')) {
            $this->error('Passwords do not match.');

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $this->option('name'), 'password' => Hash::make($password)],
        );

        $this->info(($user->wasRecentlyCreated ? 'Created' : 'Updated').' admin login for '.$email.'.');

        return self::SUCCESS;
    }
}
