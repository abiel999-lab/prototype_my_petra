<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mfa extends Model
{
    protected $connection = 'mysql'; // koneksi default BAP
    protected $table = 'mfa';

    protected $fillable = [
        'user_id',
        'mfa_enabled',
        'mfa_method',
        'two_factor_code',
        'otp_expires_at',
        'google2fa_secret',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
