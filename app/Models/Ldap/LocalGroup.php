<?php

namespace App\Models\Ldap;

use App\Models\Ldap\LocalUser;
use LdapRecord\Models\Model;

class LocalGroup extends Model
{
    protected $connection = 'local';

    /**
     * Sama seperti LocalUser, visibility HARUS protected/public,
     * jangan private, supaya tidak muncul error "must be the same or less restrictive".
     */
    public static $objectClasses = [
        'top',
        'groupOfNames',
    ];

    protected $attributes = [
        'objectClass' => [
            'top',
            'groupOfNames',
        ],
    ];

    /**
     * (Opsional) relasi ke member LDAP (LocalUser) via atribut "member".
     * Ini hanya untuk helper di kode Laravel, bukan wajib.
     */
    public function members()
    {
        return $this->hasMany(LocalUser::class, 'member');
    }
}
