<?php

namespace App\Models\Ldap;

use LdapRecord\Models\Model;
use App\Models\Ldap\LocalGroup;


class LocalUser extends Model
{
    /**
     * Pakai koneksi "local" dari config/ldap.php
     * (sesuai env: LDAP_LOCAL_CONNECTION=local).
     */
    protected $connection = 'local';

    /**
     * Override objectClasses dari parent.
     * WAJIB visibility-nya minimal sama (protected) atau lebih longgar (public).
     */
    public static $objectClasses = [
        'top',
        'person',
        'organizationalPerson',
        'inetOrgPerson',
    ];

    /**
     * Default attribute ketika entry dibuat.
     */
    protected $attributes = [
        'objectClass' => [
            'top',
            'person',
            'organizationalPerson',
            'inetOrgPerson',
        ],
    ];

    /**
     * (Opsional) Kalau kamu pakai `uid` sebagai RDN ketika create user.
     * Misal nanti kamu set:
     * LocalUser::create([
     *     'uid' => 'staff01',
     *     ...
     * ]);
     */
    public function getCreatableRdnAttribute(): string
    {
        // RDN akan menjadi: uid=<nilai uid>
        return 'uid=' . $this->uid;
    }
}
