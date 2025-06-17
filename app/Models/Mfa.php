<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mfa extends Model
{
    use HasFactory;

    protected $table = 'mfa';

    protected $fillable = [
        'user_id',
        'mfa_enabled',
        'mfa_method',
        'two_factor_code',
        'otp_expires_at',
        'google2fa_secret',
        'passwordless_enabled',
        'passwordless_token',
        'passwordless_expires_at',
        'extended_mfa_enabled',
        'extended_mfa_method',
    ];

    protected $casts = [
        'two_factor_code' => 'encrypted',
        'otp_expires_at' => 'datetime',
        'passwordless_expires_at' => 'datetime',
        'mfa_enabled' => 'boolean',
        'passwordless_enabled' => 'boolean',
        'extended_mfa_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
