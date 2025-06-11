<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LdapRecord\Container;
use LdapRecord\Models\Entry;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\Console\Command\Command as BaseCommand;

class LdapManageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'target' => 'required|in:student,default',
            'uid' => 'required|string|regex:/^[a-zA-Z0-9]{8,}$/',
            'cn' => 'required|string',
            'uidnumber' => 'required|numeric',
            'gidnumber' => 'required|numeric',
            'homedirectory' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $dn = "uid={$request->uid},ou=students,dc=petra,dc=ac,dc=id";

        $entry = new Entry([
            'uid' => $request->uid,
            'cn' => $request->cn,
            'objectclass' => ['top', 'person', 'organizationalPerson', 'inetOrgPerson', 'posixAccount'],
            'uidnumber' => $request->uidnumber,
            'gidnumber' => $request->gidnumber,
            'homedirectory' => $request->homedirectory,
            'userpassword' => $this->hashSsha($request->password),
            'description' => 'created via MyPetra UI',
        ]);

        $entry->setDn($dn);
        $entry->setConnection($request->target);

        try {
            $entry->save();
            return back()->with('success', 'User berhasil ditambahkan ke LDAP.');
        } catch (\Exception $e) {
            return back()->withErrors(['ldap' => 'Gagal menambahkan user ke LDAP: ' . $e->getMessage()]);
        }
    }

    private function hashSsha(string $password): string
    {
        $salt = random_bytes(4);
        $hash = sha1($password . $salt, true) . $salt;
        return '{SSHA}' . base64_encode($hash);
    }
}
