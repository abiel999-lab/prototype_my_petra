<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        // Contoh pengguna dan role_id yang diberikan secara manual
        $data = [
            ['user_id' => 1, 'role_id' => 1], // misalnya Admin
            ['user_id' => 1, 'role_id' => 2], // juga sebagai Staff
            ['user_id' => 2, 'role_id' => 2], // hanya Staff
            ['user_id' => 3, 'role_id' => 3], // hanya General
        ];

        foreach ($data as $item) {
            DB::table('role_user')->insert([
                'user_id' => $item['user_id'],
                'role_id' => $item['role_id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
