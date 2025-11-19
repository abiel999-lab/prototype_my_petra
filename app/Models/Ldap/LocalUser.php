<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class LocalUser extends Model
{
    protected $connection = 'local';

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
        'ou',
        'employeeType',
    ];
}
