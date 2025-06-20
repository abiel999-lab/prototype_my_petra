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
        'uuid', // <-- ini WAJIB ADA
        'ip_address',
        'device',
        'os',
        'trusted',
        'action',
        'created_at',
        'updated_at',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

