<?php

namespace App\Http\Middleware\Authorization;

use Closure;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use App\Models\TrustedDevice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewDeviceLoginMail;

class CheckNewDevice
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $ip = $request->ip();
            $agent = new Agent();
            $agent->setUserAgent($request->userAgent());

            $os = $agent->platform() ?? 'Unknown';
            $device = $agent->isDesktop() ? 'Desktop' : ($agent->isMobile() || $agent->isTablet() ? 'Phone' : 'Unknown');
            $now = Carbon::now('Asia/Jakarta');

            // Normalisasi OS
            $normalizedOS = match (true) {
                str_contains($os, 'Windows') => 'Windows',
                str_contains($os, 'Mac') => 'MacOS',
                str_contains($os, 'Android') => 'Android',
                str_contains($os, 'iOS') => 'iOS',
                str_contains($os, 'Linux') => 'Linux',
                default => $os
            };

            // Cek apakah kombinasi user + os + ip sudah pernah tercatat
            $existing = TrustedDevice::where('user_id', $user->id)
                ->where('os', $normalizedOS)
                ->where('ip_address', $ip)
                ->first();

            if (!$existing) {
                // Simpan perangkat baru
                TrustedDevice::create([
                    'user_id' => $user->id,
                    'ip_address' => $ip,
                    'device' => $device,
                    'os' => $normalizedOS,
                    'trusted' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Kirim email peringatan
                Mail::to($user->email)->send(new NewDeviceLoginMail(
                    $ip,
                    $normalizedOS,
                    $device,
                    $now->format('d M Y H:i')
                ));
            } else {
                $existing->touch(); // Perbarui last used
            }
        }

        return $next($request);
    }
}
