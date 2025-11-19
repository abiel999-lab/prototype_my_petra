<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class StudentUser extends Model
{
    protected $connection = 'student'; // koneksi 'student' di config/ldap.php

    public static $objectClasses = [
        'inetOrgPerson',
        'organizationalPerson',
        'person',
        'top',
    ];

    protected $fillable = [
        'uid',
        'cn',
        'sn',
        'mail',
    ];
}
