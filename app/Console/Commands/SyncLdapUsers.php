<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use LdapRecord\Container;
use Symfony\Component\Console\Command\Command as BaseCommand;


class SyncLdapUsers extends Command
{
    protected $signature = 'ldap:sync-users';
    protected $description = 'Sync NRP-based LDAP users (student + staff) into database';

    public function handle(): int
    {
        // Step 1: Generate and send OTP
        $otp = rand(100000, 999999);
        $adminEmail = env('MAIL_USERNAME'); // Ganti jika ingin email OTP dikirim ke email berbeda

        Mail::to($adminEmail)->send(new OtpMail($otp));
        $this->info("Kode OTP telah dikirim ke email admin: $adminEmail");

        $input = $this->ask('Masukkan OTP untuk melanjutkan');

        if ($input != $otp) {
            $this->error('OTP salah. Sinkronisasi dibatalkan.');
            return BaseCommand::FAILURE;
        }

        $this->info('OTP valid. Memulai proses sinkronisasi...');

        $inserted = 0;
        $skipped = 0;

        foreach (['default', 'student'] as $conn) {
            try {
                $entries = Container::getConnection($conn)
                    ->query()
                    ->in('dc=petra,dc=ac,dc=id')
                    ->rawFilter('(uid=*)')
                    ->get();

                foreach ($entries as $entry) {
                    $uid = $entry['uid'][0] ?? null;
                    $cn = $entry['cn'][0] ?? null;

                    // Hanya NRP yang dimulai huruf atau angka (8 digit atau huruf + 8 digit)
                    if (!$uid || !preg_match('/^([a-zA-Z]\d{8}|\d{8,})$/', $uid)) {
                        $skipped++;
                        continue;
                    }

                    $email = $uid . '@peter.petra.ac.id';

                    if (DB::table('users')->where('email', $email)->exists()) {
                        $skipped++;
                        continue;
                    }

                    DB::table('users')->insert([
                        'name' => $cn ?? $uid,
                        'email' => $email,
                        'password' => bcrypt('default_password'),
                        'usertype' => $conn,
                        'email_verified_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $inserted++;
                }

            } catch (\Exception $e) {
                $this->error("Gagal menghubungi koneksi LDAP [$conn]: " . $e->getMessage());
            }
        }

        $this->info("Sinkronisasi selesai. Berhasil: $inserted | Dilewati: $skipped");
        return BaseCommand::SUCCESS;
    }
}
