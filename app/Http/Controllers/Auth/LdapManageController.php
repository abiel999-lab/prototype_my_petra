<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LdapRecord\Container;
use LdapRecord\Models\Entry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LdapManageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('uid');

        $buildQuery = function ($connectionName) use ($search) {
            $query = Container::getConnection($connectionName)
                ->query()
                ->in('dc=petra,dc=ac,dc=id');

            if ($search) {
                $escaped = $this->ldapEscape($search);
                $query->rawFilter("(uid=*$escaped*)");
            } else {
                $query->rawFilter("(uid=*)");
            }

            return collect($query->get())
                ->filter(fn($u) => isset($u['uid'][0]))
                ->map(fn($u) => [
                    'uid' => $u['uid'][0] ?? '-',
                    'cn' => $u['cn'][0] ?? '-',
                    'dn' => $u['dn'] ?? '-',
                    'connection' => $connectionName,
                ]);
        };

        $studentUsers = $buildQuery('student');
        $staffUsers = $buildQuery('default');

        $mergedUsers = $studentUsers
            ->merge($staffUsers)
            ->unique('dn')
            ->sortBy('uid')
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $mergedUsers->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedUsers = new LengthAwarePaginator(
            $currentItems,
            $mergedUsers->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.ldap.create-new-ldap', ['allUsers' => $paginatedUsers]);
    }

    private function ldapEscape(string $value): string
    {
        return addcslashes($value, '\\*()');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'uid' => 'required|string|regex:/^[a-zA-Z0-9]{8,}$/',
            'cn' => 'required|string',
            'uidnumber' => 'required|numeric',
            'gidnumber' => 'required|numeric',
            'homedirectory' => 'required|string',
            'password' => 'nullable|string|min:6',
        ]);

        $target = $request->filled('password') ? 'student' : 'default';
        $ou = $target === 'student' ? 'students' : 'staff';
        $dn = "uid={$request->uid},ou={$ou},dc=petra,dc=ac,dc=id";

        $entryData = [
            'uid' => $request->uid,
            'cn' => $request->cn,
            'sn' => explode(' ', $request->cn)[0] ?? $request->cn,
            'objectclass' => [
                'top',
                'person',
                'organizationalPerson',
                'inetOrgPerson',
                'posixAccount',
            ],
            'uidnumber' => $request->uidnumber,
            'gidnumber' => $request->gidnumber,
            'homedirectory' => $request->homedirectory,
            'loginshell' => '/bin/bash',
            'description' => 'created via MyPetra UI',
        ];

        if ($target === 'student') {
            $entryData['userpassword'] = $this->hashSsha($request->password);
        }


        $exists = Container::getConnection($target)
            ->query()
            ->in("ou={$ou},dc=petra,dc=ac,dc=id")
            ->where('uid', '=', $request->uid)
            ->exists();

        if ($exists) {
            return back()->withErrors(['ldap' => 'UID sudah terdaftar di LDAP.']);
        }
        $entry = new Entry($entryData);
        $entry->setDn($dn);
        $entry->setConnection($target);

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

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'dn' => 'required|string',
            'connection' => 'required|in:student,default',
        ]);

        try {
            // Cari entry berdasarkan DN dan buat object model
            $entry = \LdapRecord\Models\Entry::find($request->dn);

            if ($entry) {
                $entry->setConnection($request->connection); // penting
                $entry->delete(); // panggil delete dari model

                return back()->with('success', 'User berhasil dihapus dari LDAP.');
            } else {
                return back()->withErrors(['ldap' => 'User tidak ditemukan di LDAP.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['ldap' => 'Gagal menghapus user: ' . $e->getMessage()]);
        }
    }


}
