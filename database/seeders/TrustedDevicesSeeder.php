<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                'user_id' => 1,
                'uuid' => (string) Str::uuid(),
                'ip_address' => '192.168.1.10',
                'device' => 'Desktop',
                'os' => 'Windows',
                'trusted' => 0,
                'action' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 1,
                'uuid' => (string) Str::uuid(),
                'ip_address' => '192.168.1.12',
                'device' => 'Desktop',
                'os' => 'Windows',
                'trusted' => 0,
                'action' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 1,
                'uuid' => (string) Str::uuid(),
                'ip_address' => '192.168.1.11',
                'device' => 'Phone',
                'os' => 'Android',
                'trusted' => 0,
                'action' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
