<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        if (!$user->mfa()->exists()) {
            $user->mfa()->create([
                'mfa_enabled' => false,
                'mfa_method' => 'email',
            ]);
        }
    }
}
