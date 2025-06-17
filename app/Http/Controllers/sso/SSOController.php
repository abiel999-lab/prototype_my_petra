<?php

namespace App\Http\Controllers\sso;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;

class SSOController extends Controller
{
    public function redirectToBap(): RedirectResponse
    {
        $user = Auth::user();

        $payload = [
            'email' => $user->email,
            'timestamp' => now()->timestamp,
        ];

        $token = Crypt::encrypt($payload);

        return redirect()->away('http://localhost:8002/sso-login?token=' . urlencode($token));
    }
}
