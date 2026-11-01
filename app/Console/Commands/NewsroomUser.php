<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NewsroomUser extends Command
{
    protected $signature = 'newsroom:user {email} {--name=Editor} {--role=editor} {--generate : Generate a random password}';

    protected $description = 'Create a newsroom user or reset their access. Existing accounts require confirmation.';

    public function handle(): int
    {
        $role = $this->option('role');
        if (! in_array($role, ['admin', 'editor', 'reporter', 'producer'], true)) {
            $this->error('Invalid role.');

            return self::FAILURE;
        }
        validator(['email' => $this->argument('email')], ['email' => 'required|email'])->validate();
        $user = User::firstWhere('email', $this->argument('email'));
        if ($user && ! $this->confirm('Reset the existing account password and role?')) {
            return self::FAILURE;
        }
        $password = $this->option('generate') ? Str::password(24) : $this->secret('Password (at least 12 characters)');
        if (strlen($password ?? '') < 12) {
            $this->error('Use at least 12 characters.');

            return self::FAILURE;
        }
        $user ??= new User;
        $user->forceFill(['name' => $this->option('name'), 'email' => $this->argument('email'), 'role' => $role, 'password' => $password])->save();
        $this->info('Newsroom access created.');
        if ($this->option('generate')) {
            $this->line($password);
        }

        return self::SUCCESS;
    }
}
