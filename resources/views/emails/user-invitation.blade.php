<x-mail::message>
# Undangan Bergabung ke Ayyanet Ticketing

Anda telah diundang untuk bergabung dengan platform **Ayyanet Ticketing** sebagai **{{ ucfirst($invitation->role) }}**.

Untuk melengkapi pendaftaran dan membuat password akun Anda, silakan klik tombol di bawah ini:

<x-mail::button :url="$inviteUrl">
Buat Akun Anda
</x-mail::button>

*Link undangan ini hanya berlaku selama 5 jam sejak dikirimkan.*

Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:
{{ $inviteUrl }}

Terima kasih,<br>
**Tim Ayyanet**
</x-mail::message>
