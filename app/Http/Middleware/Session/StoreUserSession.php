<?php

namespace App\Http\Middleware\Session;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StoreUserSession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $sessionId = session()->getId(); // Get current session ID

            // Check if this session already exists
            $existingSession = DB::table('sessions')->where('id', $sessionId)->first();

            if ($existingSession) {
                // Set Jakarta timezone (WIB)
                $now = Carbon::now('Asia/Jakarta');

                // Store login time once, if not set
                if (!session()->has('login_time')) {
                    session()->put('login_time', $now->toDateTimeString());
                }

                // Store expiration time once, if not set (use copy() to prevent modifying $now)
                if (!session()->has('expires_at')) {
                    session()->put('expires_at', $now->copy()->addMinutes(intval(config('session.lifetime')))->toDateTimeString());
                }

                // Ensure user_id is set for session
                DB::table('sessions')->where('id', $sessionId)->update([
                    'user_id' => Auth::id(),
                ]);
            }
        }

        return $next($request);
    }
}
