<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\User;
use App\Services\LoggingService;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');

        // 🔹 Attempt Laravel Database Authentication First
        if (Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // 🔹 If Database Authentication Fails, Attempt LDAP Authentication
        try {
            $ldapUser = LdapUser::where('mail', $credentials['email'])->first();

            if ($ldapUser && $ldapUser->authenticate($credentials['password'])) {
                // Sync LDAP user into Laravel database
                $user = User::updateOrCreate(
                    ['email' => $ldapUser->mail[0]],
                    [
                        'name' => $ldapUser->cn[0] ?? 'Unknown',
                        'password' => Hash::make($credentials['password']), // Store hashed password
                        'usertype' => 'general', // Default role for LDAP users
                    ]
                );

                Auth::login($user);
                RateLimiter::clear($this->throttleKey());
                return;
            }
        } catch (\Exception $e) {
            LoggingService::logSecurityViolation("LDAP login failed for email" . $e->getMessage());
        }

        // 🔹 If both Database and LDAP Authentication Fail
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
