<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class StaffUser extends Model
{
    protected $connection = 'default'; // koneksi 'default' di config/ldap.php

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
