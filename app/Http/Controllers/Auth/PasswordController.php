<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Services\LoggingService;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Models\Entry;


class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);
        LoggingService::logMfaEvent("User [ID: {$request->user()->id}] changed password", [
            'email' => $request->user()->email,
        ]);

        // 🔐 Sync password ke Active Directory (LDAP)
        try {
            $ldapUser = Entry::where('mail', '=', $request->user()->email)->first();

            if ($ldapUser instanceof LdapUser) {
                $quotedPwd = iconv('UTF-8', 'UTF-16LE', '"' . $validated['password'] . '"');
                $ldapUser->unicodePwd = $quotedPwd;
                $ldapUser->save();

                LoggingService::logMfaEvent("Password synced to LDAP for {$request->user()->email}", []);
            }
        } catch (\Exception $e) {
            LoggingService::logSecurityViolation("LDAP password sync failed for {$request->user()->email}: " . $e->getMessage(), []);
        }


        return back()->with('status', 'password-updated');
    }
}
