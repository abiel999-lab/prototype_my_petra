<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExternalUser extends Model
{
    protected $connection = 'mysql_my_petra'; // nama koneksi database lain
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = true;
}

