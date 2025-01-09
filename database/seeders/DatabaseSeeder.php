<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
{
    User::create([
        'name' => 'Abiel Nathanael Georgius Pasaribu',
        'email' => 'c14210157@john.petra.ac.id',
        'password' => bcrypt('23/04/2003'),
    ]);
}
}
