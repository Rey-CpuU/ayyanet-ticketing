<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\StatusBanner;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Services\TicketWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (! $user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Backfill roles: first user (owner) becomes admin, the rest CS.
        $owner = User::orderBy('id')->first();
        if ($owner) {
            $owner->update(['role' => 'admin']);
        }
        User::whereNull('role')->orWhere('role', '')->update(['role' => 'cs']);

        // Ensure a few CS agents exist so assignment tags feel real.
        $cs = User::firstOrCreate(
            ['email' => 'cs1@ayyanet.test'],
            ['name' => 'Andi Saputra', 'password' => bcrypt('password'), 'role' => 'cs', 'email_verified_at' => now()],
        );
        $cs2 = User::firstOrCreate(
            ['email' => 'cs2@ayyanet.test'],
            ['name' => 'Rina Marlina', 'password' => bcrypt('password'), 'role' => 'cs', 'email_verified_at' => now()],
        );
        $cs3 = User::firstOrCreate(
            ['email' => 'cs3@ayyanet.test'],
            ['name' => 'Budi Hartono', 'password' => bcrypt('password'), 'role' => 'cs', 'email_verified_at' => now()],
        );
        $agents = collect([$cs, $cs2, $cs3]);

        // Tickets and customers are soft-deletable: purge them for real so the demo numbers
        // can be reused (tickets first, the customer FK restricts deletes).
        TicketMessage::query()->delete();
        Ticket::withTrashed()->forceDelete();
        Customer::withTrashed()->forceDelete();
        StatusBanner::query()->delete();

        $customers = collect([
            ['name' => 'Budi Santoso', 'phone' => '0812-3456-7801', 'address' => 'Jl. Melati No. 12, Jakarta', 'package' => 'Home 10Mbps'],
            ['name' => 'Siti Rahayu', 'phone' => '0813-9876-5402', 'address' => 'Jl. Kenanga No. 45, Bandung', 'package' => 'Home 20Mbps'],
            ['name' => 'Agus Wijaya', 'phone' => '0821-1122-3344', 'address' => 'Perum Griya Asri Blok C3, Surabaya', 'package' => 'Business 50Mbps'],
            ['name' => 'Dewi Lestari', 'phone' => '0857-5566-7788', 'address' => 'Jl. Anggrek No. 8, Yogyakarta', 'package' => 'Home 10Mbps'],
            ['name' => 'Rudi Hartono', 'phone' => '0811-2233-4455', 'address' => 'Jl. Cempaka No. 21, Semarang', 'package' => 'Home 20Mbps'],
            ['name' => 'Maya Putri', 'phone' => '0819-8877-6655', 'address' => 'Jl. Dahlia No. 3, Medan', 'package' => 'Business 100Mbps'],
            ['name' => 'Joko Prasetyo', 'phone' => '0815-4433-2211', 'address' => 'Jl. Flamboyan No. 67, Makassar', 'package' => 'Home 10Mbps'],
            ['name' => 'Rina Kartika', 'phone' => '0822-9988-7766', 'address' => 'Jl. Mawar No. 14, Denpasar', 'package' => 'Home 20Mbps'],
        ]);

        $customers = $customers->map(fn ($c) => Customer::create($c));

        $tickets = [
            ['title' => 'Internet sering putus di malam hari', 'description' => 'Koneksi internet terputus setiap malam sekitar pukul 21.00 - 23.00, sudah 3 hari berturut-turut. Saat dicek modem tetap menyala tapi tidak ada koneksi.', 'category' => 'Internet', 'olt' => 'OLT-01', 'location' => 'Port 12', 'priority' => 'High', 'status' => 'Open', 'daysAgo' => 0, 'unassigned' => true],
            ['title' => 'Kecepatan internet tidak sesuai paket', 'description' => 'Berlangganan paket Home 20Mbps tapi kecepatan yang didapat hanya sekitar 5Mbps. Sudah coba restart modem dan tes via kabel, hasil tetap sama.', 'category' => 'Internet', 'olt' => 'OLT-02', 'location' => 'Port 5', 'priority' => 'Medium', 'status' => 'Checking', 'daysAgo' => 1],
            ['title' => 'Tagihan bulan ini terasa lebih besar', 'description' => 'Tagihan bulan ini lebih besar dari biasanya padahal pemakaian sama seperti bulan sebelumnya. Mohon dijelaskan rincian tagihannya.', 'category' => 'Billing', 'olt' => null, 'location' => null, 'priority' => 'Low', 'status' => 'Waiting Customer', 'daysAgo' => 1, 'unassigned' => true],
            ['title' => 'Tidak bisa upload file ke cloud office', 'description' => 'Sejak kemarin tidak bisa upload file ke layanan cloud kantor. Browsing dan download normal, hanya upload yang gagal di tengah proses.', 'category' => 'Internet', 'olt' => 'OLT-03', 'location' => 'Port 8', 'priority' => 'High', 'status' => 'Escalated', 'daysAgo' => 2],
            ['title' => 'Permintaan pindah alamat', 'description' => 'Bulan depan pindah rumah ke alamat baru. Mohon info prosedur dan biaya pemindahan layanan internet.', 'category' => 'Layanan', 'olt' => null, 'location' => null, 'priority' => 'Low', 'status' => 'Open', 'daysAgo' => 0],
            ['title' => 'Modem rusak (indikator tidak menyala)', 'description' => 'Modem tidak menyala sama sekali setelah hujan deras kemarin malam. Sudah dicoba ganti adaptor, tetap tidak menyala. Mohon penggantian unit.', 'category' => 'Hardware', 'olt' => 'OLT-01', 'location' => 'Port 20', 'priority' => 'Medium', 'status' => 'Checking', 'daysAgo' => 2],
            ['title' => 'Lambat saat jam kantor', 'description' => 'Kecepatan internet turun drastis saat jam 09.00-16.00. Di luar jam tersebut normal kembali.', 'category' => 'Internet', 'olt' => 'OLT-04', 'location' => 'Port 3', 'priority' => 'Medium', 'status' => 'Waiting Customer', 'daysAgo' => 3],
            ['title' => 'Minta upgrade ke paket lebih besar', 'description' => 'Ingin upgrade dari Home 20Mbps ke Business 50Mbps untuk kebutuhan WFH. Mohon info ketersediaan dan prosesnya.', 'category' => 'Layanan', 'olt' => null, 'location' => null, 'priority' => 'Low', 'status' => 'Solved', 'daysAgo' => 3],
            ['title' => 'Intermiten connection loss sejak update firmware', 'description' => 'Setelah modem update firmware otomatis, koneksi sering terputus 1-2 menit lalu kembali normal. Terjadi setiap 15-20 menit.', 'category' => 'Hardware', 'olt' => 'OLT-02', 'location' => 'Port 15', 'priority' => 'High', 'status' => 'Solved', 'daysAgo' => 4],
            ['title' => 'Lupa password WiFi', 'description' => 'Tidak bisa menemukan password WiFi yang tertera di modem. Mohon bantuan reset password.', 'category' => 'Layanan', 'olt' => null, 'location' => null, 'priority' => 'Low', 'status' => 'Closed', 'daysAgo' => 5],
            ['title' => 'Gangguan total di area Perumnas', 'description' => 'Seluruh pelanggan di area Perumnas tidak ada koneksi sama sekali sejak pagi. Kemungkinan kabel ODP utama terputus.', 'category' => 'Internet', 'olt' => 'OLT-01', 'location' => 'ODP-07', 'priority' => 'High', 'status' => 'Open', 'daysAgo' => 0, 'unassigned' => true],
        ];

        $messagePool = [
            'Sudah saya cek dari sisi jaringan, mohon tunggu konfirmasi lebih lanjut.',
            'Kami sudah meneruskan laporan ini ke tim teknisi.',
            'Mohon info nomor HP yang bisa dihubungi untuk penjadwalan teknisi.',
            'Teknisi sudah kami jadwalkan besok pagi pukul 09.00.',
            'Setelah pengecekan, kemungkinan ada masalah di kabel ODP. Tim sedang menangani.',
            'Mohon maaf atas kendalanya, kami sedang mengupayakan perbaikan secepatnya.',
            'Permintaan upgrade sudah kami proses, terima kasih sudah menunggu.',
            'Masalah sudah teratasi setelah penggantian kabel, mohon konfirmasi jika masih bermasalah.',
        ];

        foreach ($tickets as $i => $data) {
            $daysAgo = $data['daysAgo'];
            $unassigned = $data['unassigned'] ?? false;
            unset($data['daysAgo'], $data['unassigned']);

            $createdAt = now()->subDays($daysAgo)->subHours(2);

            $ticket = Ticket::create(array_merge($data, [
                'ticket_number' => 'TMP-'.Str::uuid(),
                'customer_id' => $customers[$i % $customers->count()]->id,
                'created_by' => $user->id,
                'assigned_to' => $unassigned ? null : $agents[$i % $agents->count()]->id,
            ], TicketWorkflow::initialSla($data['priority'], $createdAt)));

            // Same numbering as tickets created through the app; backdate for a realistic history.
            $ticket->forceFill([
                'ticket_number' => TicketWorkflow::numberFor($ticket->id),
                'created_at' => $createdAt,
                'updated_at' => now()->subDays($daysAgo)->subMinutes(30),
            ])->saveQuietly();

            $messageCount = rand(2, 4);
            for ($m = 0; $m < $messageCount; $m++) {
                TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'message' => $messagePool[array_rand($messagePool)],
                    'created_at' => $ticket->created_at->addHours(($m + 1) * 3),
                    'updated_at' => $ticket->created_at->addHours(($m + 1) * 3),
                ]);
            }
        }

        StatusBanner::create([
            'title' => 'Maintenance Terjadwal Malam Ini',
            'message' => 'Akan ada maintenance jaringan pada pukul 23.00–01.00. Koneksi internet mungkin terputus sementara di area tertentu.',
            'type' => 'maintenance',
            'is_active' => true,
        ]);

        StatusBanner::create([
            'title' => 'Gangguan di Area Bandung',
            'message' => 'Sedang ada gangguan di area Bandung Utara. Tim teknis sedang menangani. Mohon tidak membuat tiket duplikat.',
            'type' => 'outage',
            'is_active' => true,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHours(5),
        ]);
    }
}
