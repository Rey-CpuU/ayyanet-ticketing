<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Peringatan Keamanan</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0c0e14; color: #f9f6f0; margin: 0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background-color: #141721; border: 1px solid #2a2e3d; border-radius: 8px; padding: 28px;">
        <div style="display: flex; align-items: center; margin-bottom: 20px;">
            <h2 style="color: #ef4444; margin: 0; font-size: 18px;">⚠️ Peringatan Keamanan Akun</h2>
        </div>

        <p style="font-size: 14px; line-height: 1.6; color: #d1d5db;">
            Halo, kami mendeteksi adanya <strong>percobaan login gagal berturut-turut (5 kali)</strong> ke akun Anda di sistem <strong>Ayyanet Ticketing</strong>.
        </p>

        <div style="background-color: #1e2230; border-left: 4px solid #ef4444; padding: 14px 16px; margin: 20px 0; border-radius: 4px; font-size: 13px; font-family: monospace;">
            <p style="margin: 4px 0;"><strong>Akun Email:</strong> {{ $targetEmail }}</p>
            <p style="margin: 4px 0;"><strong>Alamat IP:</strong> {{ $ipAddress }}</p>
            <p style="margin: 4px 0;"><strong>Waktu:</strong> {{ $timestamp }}</p>
        </div>

        <p style="font-size: 13.5px; line-height: 1.6; color: #9ca3af;">
            Sebagai langkah pencegahan, sistem telah mengunci sementara form login untuk IP tersebut. Jika ini bukan Anda, disarankan untuk segera mengubah password akun Anda setelah bisa login kembali.
        </p>

        <hr style="border: 0; border-top: 1px solid #2a2e3d; margin: 24px 0;">

        <p style="font-size: 11.5px; color: #6b7280; margin: 0; text-align: center;">
            Email otomatis ini dikirim oleh sistem keamanan Ayyanet Ticketing.
        </p>
    </div>
</body>
</html>
