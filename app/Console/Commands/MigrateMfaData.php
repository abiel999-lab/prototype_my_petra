<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Mfa;

class MigrateMfaData extends Command
{
    protected $signature = 'migrate:mfa';
    protected $description = 'Move MFA-related fields from users table to mfa table';

    public function handle()
    {
        $users = User::all();
        foreach ($users as $user) {
            Mfa::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'mfa_enabled' => $user->mfa_enabled,
                    'mfa_method' => $user->mfa_method,
                    'two_factor_code' => $user->two_factor_code,
                    'otp_expires_at' => $user->otp_expires_at,
                    'google2fa_secret' => $user->google2fa_secret,
                    'passwordless_enabled' => $user->passwordless_enabled,
                    'passwordless_token' => $user->passwordless_token ?? null,
                    'passwordless_expires_at' => $user->passwordless_expires_at ?? null,
                ]
            );
        }

        $this->info('MFA data migrated successfully!');
    }
}

