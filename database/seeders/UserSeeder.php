<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
{
    User::create([
        'name' => 'Abiel Nathanael Georgius Pasaribu',
        'email' => 'c14210157@john.petra.ac.id',
        'usertype' => 'student',
        'password' => bcrypt('23/04/2003'),
        'email_verified_at' => now(),
        'remember_token' => Str::random(60),
        'mfa_enabled' => false,
        'mfa_method' => 'email'
    ],);
    User::create([
        'name' => 'James Efandaru',
        'email' => 'efandaru@peter.petra.ac.id',
        'usertype' => 'staff',
        'password' => bcrypt('23/04/2003'),
        'email_verified_at' => now(),
        'remember_token' => Str::random(60),
        'mfa_enabled' => false,
        'mfa_method' => 'email'
    ],);
    User::create([
        'name' => 'Wilson Lim',
        'email' => 'wilson@gmail.com',
        'usertype' => 'general',
        'password' => bcrypt('23/04/2003'),
        'email_verified_at' => now(),
        'remember_token' => Str::random(60),
        'mfa_enabled' => false,
        'mfa_method' => 'email'
    ],);
    User::create([
        'name' => 'Peter Jackson',
        'email' => 'peter@petra.ac.id',
        'usertype' => 'admin',
        'password' => bcrypt('23/04/2003'),
        'email_verified_at' => now(),
        'remember_token' => Str::random(60),
        'mfa_enabled' => false,
        'mfa_method' => 'email'
    ],);
}
}
