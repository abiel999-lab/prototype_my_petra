<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrustedDevicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('trusted_devices')->insert([
            [
                'user_id' => 5,
                'ip_address' => '192.168.1.10',
                'device' => 'Laptop',
                'os' => 'MacOS',
                'trusted' => 0,
                'action' => NULL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 5,
                'ip_address' => '192.168.1.11',
                'device' => 'Desktop',
                'os' => 'Linux',
                'trusted' => 0,
                'action' => NULL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 5,
                'ip_address' => '192.168.1.12',
                'device' => 'Tablet',
                'os' => 'Android',
                'trusted' => 0,
                'action' => NULL,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
