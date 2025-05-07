<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticketing extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code',
        'name',
        'email',
        'phone_number',
        'issue_type',
        'message',
        'attachment',
        'ip_address',
    ];
}
