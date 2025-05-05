<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Mfa;

class SyncMissingMfa extends Command
{
    protected $signature = 'mfa:sync-missing';
    protected $description = 'Menambahkan MFA default pada user yang belum memiliki entri di tabel mfa';

    public function handle()
    {
        $users = User::doesntHave('mfa')->get();

        if ($users->isEmpty()) {
            $this->info('✅ Semua user sudah punya MFA.');
            return;
        }

        foreach ($users as $user) {
            if (!$user->mfa()->exists()) {
                $user->mfa()->create([
                    'mfa_enabled' => false,
                    'mfa_method' => 'email',
                ]);

                $this->info("✔️  MFA dibuat untuk: {$user->email}");
            }
        }

        $this->info("✅ Total ditambahkan: {$users->count()} user.");
    }
}
