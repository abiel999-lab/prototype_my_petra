<?php
$ldap_host = "ldap.petra.ac.id";
$ldap_port = 389;
$ldap_dn = "uid=jaringan,ou=staff,dc=petra,dc=ac,dc=id";
$ldap_password = "petra123";

// Koneksi ke server LDAP
$ldap_conn = ldap_connect($ldap_host, $ldap_port);
if (!$ldap_conn) {
    die("Koneksi ke server LDAP gagal!");
}

ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

// Coba autentikasi (bind)
$bind = ldap_bind($ldap_conn, $ldap_dn, $ldap_password);
if ($bind) {
    echo "Autentikasi LDAP berhasil!";
} else {
    echo "Autentikasi LDAP gagal! Error: " . ldap_error($ldap_conn);
}
?>
