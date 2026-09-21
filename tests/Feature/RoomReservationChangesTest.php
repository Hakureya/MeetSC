<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomReservation;
use App\Models\User;
use App\Support\TimeSlots;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomReservationChangesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Uji Coba',
            'email' => 'uji@test.id',
            'password' => bcrypt('rahasia'),
            'divisi' => 'Renbis',
            'role' => 'user',
        ]);

        $this->room = Room::create([
            'name' => 'Ruang Uji',
            'floor' => 1,
            'capacity_min' => 6,
            'capacity_max' => 12,
            'is_combined' => false,
        ]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'room_id' => $this->room->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '08:30',
            'keperluan' => 'Rapat uji',
            'nama_pic' => 'Budi',
            'no_telp_pic' => '081234567890',
            'divisi_pic' => 'PDSI',
            'jumlah_peserta' => 5,
            'konsumsi' => 'Snack',
        ], $override);
    }

    private function book(array $override = [])
    {
        return $this->actingAs($this->user)->post(route('rooms.store'), $this->payload($override));
    }

    /** Satu slot waktu (30 menit) kini sah dipesan. */
    public function test_reservasi_satu_slot_waktu_diterima(): void
    {
        $this->book(['jam_mulai' => '08:00', 'jam_selesai' => '08:30'])
            ->assertSessionHasNoErrors();

        // Substr dipakai karena MySQL menyimpan TIME sebagai "08:00:00"
        // sedangkan SQLite (dipakai saat testing) menyimpan "08:00".
        $r = RoomReservation::first();
        $this->assertSame('08:00', substr($r->jam_mulai, 0, 5));
        $this->assertSame('08:30', substr($r->jam_selesai, 0, 5));
    }

    /** Rentang beberapa slot tetap berfungsi seperti sebelumnya. */
    public function test_reservasi_rentang_banyak_slot_tetap_diterima(): void
    {
        $this->book(['jam_mulai' => '09:00', 'jam_selesai' => '11:00'])
            ->assertSessionHasNoErrors();

        $r = RoomReservation::first();
        $this->assertSame('09:00', substr($r->jam_mulai, 0, 5));
        $this->assertSame('11:00', substr($r->jam_selesai, 0, 5));
    }

    public function test_validasi_slot_menerima_satu_slot(): void
    {
        $this->assertNull(TimeSlots::validateRange('08:00', '08:30'));
        $this->assertNull(TimeSlots::validateRange('08:00', '16:30'));
        $this->assertNotNull(TimeSlots::validateRange('09:00', '08:30'));
        $this->assertNotNull(TimeSlots::validateRange('07:00', '08:30'));
    }

    public function test_divisi_pic_tersimpan(): void
    {
        $this->book(['divisi_pic' => 'Niaga'])->assertSessionHasNoErrors();

        $this->assertSame('Niaga', RoomReservation::first()->divisi_pic);
    }

    public function test_divisi_pic_wajib_diisi(): void
    {
        $payload = $this->payload();
        unset($payload['divisi_pic']);

        $this->actingAs($this->user)
            ->post(route('rooms.store'), $payload)
            ->assertSessionHasErrors('divisi_pic');
    }

    /** Ruang gabungan: reservasi turunan ikut membawa Divisi PIC. */
    public function test_divisi_pic_ikut_tersalin_ke_reservasi_turunan(): void
    {
        $lain = Room::create([
            'name' => 'Ruang Lain', 'floor' => 1,
            'capacity_min' => 6, 'capacity_max' => 6, 'is_combined' => false,
        ]);

        $gabungan = Room::create([
            'name' => 'Ruang Uji + Lain', 'floor' => 1,
            'capacity_min' => 12, 'capacity_max' => 18, 'is_combined' => true,
            'combined_room_ids' => [$this->room->id, $lain->id],
        ]);

        $this->book(['room_id' => $gabungan->id, 'divisi_pic' => 'K3L'])
            ->assertSessionHasNoErrors();

        $turunan = RoomReservation::where('auto_generated', true)->get();

        $this->assertCount(2, $turunan);
        foreach ($turunan as $t) {
            $this->assertSame('K3L', $t->divisi_pic);
        }
    }

    /** Opsi terakhir "Other" disimpan sebagai teks bebas yang diketik pemesan. */
    public function test_konsumsi_other_disimpan_sebagai_teks_manual(): void
    {
        $this->book([
            'konsumsi' => 'Other',
            'konsumsi_lainnya' => 'Nasi kotak dan kopi',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Nasi kotak dan kopi', RoomReservation::first()->konsumsi);
    }

    public function test_konsumsi_other_wajib_diisi_manual(): void
    {
        $this->book(['konsumsi' => 'Other'])
            ->assertSessionHasErrors('konsumsi_lainnya');

        $this->assertSame(0, RoomReservation::count());
    }

    /** Opsi ini sebelumnya selalu gagal validasi karena tidak cocok dengan ENUM. */
    public function test_opsi_disiapkan_pln_kini_diterima(): void
    {
        $this->book(['konsumsi' => 'Disiapkan PLN NP/PLN IP/Eksternal'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Disiapkan PLN NP/PLN IP/Eksternal', RoomReservation::first()->konsumsi);
    }

    public function test_konsumsi_di_luar_daftar_ditolak(): void
    {
        $this->book(['konsumsi' => 'Sembarang'])->assertSessionHasErrors('konsumsi');
    }

    /** Halaman daftar ruangan menampilkan keterangan kapasitas. */
    public function test_halaman_daftar_ruangan_menampilkan_kapasitas(): void
    {
        $this->actingAs($this->user)
            ->get(route('rooms.index'))
            ->assertOk()
            ->assertSee('Kapasitas 6 hingga 12 orang');
    }

    /** Form pemesanan memuat isian Divisi PIC dan opsi Other. */
    public function test_form_pemesanan_memuat_divisi_pic_dan_opsi_other(): void
    {
        $this->actingAs($this->user)
            ->get(route('rooms.create', ['room' => $this->room->id]))
            ->assertOk()
            ->assertSee('Divisi PIC')
            ->assertSee('name="divisi_pic"', false)
            ->assertSee('name="konsumsi_lainnya"', false);
    }
}
