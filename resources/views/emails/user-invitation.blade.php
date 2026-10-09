<x-mail::message>
# Selamat datang, {{ $user->name }}!

Akun Anda di portal **Ayyanet Support Desk** telah dibuat oleh admin.

## Kredensial Login

- **Email:** {{ $user->email }}
- **Password sementara:** `{{ $temporaryPassword }}`

Masuk di portal dan segera ganti password Anda melalui menu **Profile** setelah login pertama.

Jika Anda merasa tidak seharusnya menerima email ini, abaikan saja.

Terima kasih,<br>
**Tim Ayyanet**
</x-mail::message>
