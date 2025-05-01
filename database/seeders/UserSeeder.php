<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Abiel Nathanael Georgius Pasaribu',
                'email' => 'c14210157@john.petra.ac.id',
                'usertype' => 'student',
            ],
            [
                'name' => 'James Efandaru',
                'email' => 'efandaru@peter.petra.ac.id',
                'usertype' => 'staff',
            ],
            [
                'name' => 'Wilson Lim',
                'email' => 'wilson@gmail.com',
                'usertype' => 'general',
            ],
            [
                'name' => 'Peter Jackson',
                'email' => 'peter@petra.ac.id',
                'usertype' => 'admin',
            ]
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'usertype' => $userData['usertype'],
                'password' => bcrypt('23/04/2003'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(60),
            ]);

            // Insert MFA config into 'mfa' table
            DB::table('mfa')->insert([
                'user_id' => $user->id,
                'mfa_enabled' => false,
                'mfa_method' => 'email',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

