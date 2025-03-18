<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TrustedDevice extends Model
{
    use HasFactory;

    protected $table = 'trusted_devices';

    protected $fillable = [
        'user_id',
        'ip_address',
        'device',
        'os',
        'trusted', // Matches column name in migration
        'action',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

