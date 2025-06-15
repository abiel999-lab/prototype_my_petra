<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>New Device Login Detected</title>
    <style>
        img {
            display: block;
            margin-left: auto;
            margin-right: auto;
            padding: 20px;
            max-width: 100%;
            border: none;
            height: 75px;
            max-height: 75px;
        }
    </style>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px;">
    <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="Petra Logo">

    <table width="100%" cellspacing="0" cellpadding="0"
        style="color: #000; max-width: 600px; margin: auto; background: #ffffff; padding: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">

        <tr>
            <td>
                <h2>Halo, {{ explode(' ', $userName)[0] }}!</h2>
                <p>Kami mendeteksi bahwa akun Anda telah login dari perangkat baru yang belum pernah dikenali sebelumnya.</p>

                <p><strong>Alamat IP:</strong> {{ $ip }}</p>
                <p><strong>Sistem Operasi:</strong> {{ $os }}</p>
                <p><strong>Jenis Perangkat:</strong> {{ $device }}</p>
                <p><strong>Waktu Akses:</strong> {{ $time }}</p>

                <h3 align="center" style="font-size: 18px; font-weight: bold; color: red; margin-top: 20px;">
                    Apakah ini Anda?
                </h3>

                <p style="margin-top: 10px;">
                    Jika Anda tidak mengenali aktivitas ini, segera ubah kata sandi Anda dan hubungi tim keamanan kami untuk mencegah akses tidak sah.
                </p>

                <p style="margin-top: 20px;">Salam hormat,<br><strong>Tim Keamanan Universitas Kristen Petra</strong></p>
            </td>
        </tr>
    </table>
</body>

</html>
