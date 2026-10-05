<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomReservation;
use App\Models\User;
use App\Models\ZoomReservation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menjaga perbaikan efisiensi: sinkronisasi status dibatasi per menit, dan jumlah query
 * halaman tidak boleh bertambah seiring bertambahnya data.
 */
class EfficiencyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-05 10:00:00');

        $this->user = User::create([
            'name' => 'Budi', 'email' => 'budi@test.id', 'password' => 'x',
            'divisi' => 'PDSI', 'role' => 'user',
        ]);
    }

    private function room(string $name = 'Ruang A', bool $combined = false, array $ids = []): Room
    {
        return Room::create([
            'name' => $name, 'floor' => 1, 'capacity_min' => 6, 'capacity_max' => 6,
            'is_combined' => $combined, 'combined_room_ids' => $combined ? $ids : null,
        ]);
    }

    private function roomReservation(Room $room, string $start, string $end, string $status = 'mendatang', ?int $parent = null): RoomReservation
    {
        return RoomReservation::create([
            'room_id' => $room->id, 'user_id' => $this->user->id, 'tanggal' => '2026-10-05',
            'jam_mulai' => $start, 'jam_selesai' => $end, 'keperluan' => 'Rapat', 'nama_pic' => 'P',
            'no_telp_pic' => '1', 'divisi_pic' => 'D', 'jumlah_peserta' => 3, 'konsumsi' => 'Snack',
            'status' => $status, 'parent_reservation_id' => $parent, 'auto_generated' => $parent !== null,
        ]);
    }

    private function queryCount(callable $request): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $request();
        $n = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $n;
    }

    public function test_sinkronisasi_status_hanya_berjalan_sekali_per_interval(): void
    {
        $room = $this->room();
        $first = $this->roomReservation($room, '08:00', '09:00');

        $this->actingAs($this->user)->get(route('dashboard'))->assertOk();
        $this->assertSame('selesai', $first->fresh()->status);

        // Reservasi lain kedaluwarsa sesudahnya: request berikutnya dalam interval TIDAK menjalankan sinkronisasi.
        $second = $this->roomReservation($room, '09:00', '09:30');
        $this->actingAs($this->user)->get(route('dashboard'))->assertOk();
        $this->assertSame('mendatang', $second->fresh()->status);

        // Setelah interval lewat, sinkronisasi berjalan lagi.
        Carbon::setTestNow(now()->addSeconds(61));
        $this->actingAs($this->user)->get(route('dashboard'))->assertOk();
        $this->assertSame('selesai', $second->fresh()->status);
    }

    public function test_sinkronisasi_juga_berlaku_untuk_zoom(): void
    {
        $zoom = ZoomReservation::create([
            'user_id' => $this->user->id, 'nama_agenda' => 'Z', 'nama_pic' => 'P', 'no_telp_pic' => '1',
            'divisi_pic' => 'D', 'tanggal' => '2026-10-05', 'jam_mulai' => '08:00', 'jam_selesai' => '09:00',
            'room_number' => 1, 'status' => 'mendatang',
        ]);

        $this->actingAs($this->user)->get(route('dashboard'))->assertOk();

        $this->assertSame('selesai', $zoom->fresh()->status);
    }

    public function test_query_landing_tidak_bertambah_seiring_jumlah_ruang(): void
    {
        $a = $this->room('Ruang 1');
        $this->roomReservation($a, '09:30', '10:30');
        $this->get('/')->assertOk(); // pemanasan: request pertama menjalankan sinkronisasi status
        $few = $this->queryCount(fn () => $this->get('/')->assertOk());

        foreach (range(2, 9) as $i) {
            $this->roomReservation($this->room("Ruang {$i}"), '09:30', '10:30');
        }
        $many = $this->queryCount(fn () => $this->get('/')->assertOk());

        $this->assertSame($few, $many);
    }

    public function test_landing_menandai_ruang_yang_sedang_dipakai(): void
    {
        $busy = $this->room('Ruang Sibuk');
        $later = $this->room('Ruang Nanti');
        $this->roomReservation($busy, '09:30', '10:30');   // sekarang 10:00 -> sedang dipakai
        $this->roomReservation($later, '13:00', '14:00');  // belum mulai

        $rooms = collect($this->get('/')->assertOk()->viewData('rooms'))->keyBy('name');

        $this->assertTrue($rooms['Ruang Sibuk']['in_use']);
        $this->assertFalse($rooms['Ruang Nanti']['in_use']);
        $this->assertSame(['13:00 – 14:00'], $rooms['Ruang Nanti']['schedules']);
    }

    public function test_query_dashboard_tidak_bertambah_seiring_jumlah_reservasi(): void
    {
        $a = $this->room('Ruang A');
        $b = $this->room('Ruang B');
        $combined = $this->room('Ruang A+B', true, [$a->id, $b->id]);

        $parent = $this->roomReservation($combined, '15:00', '16:00');
        $this->roomReservation($a, '15:00', '16:00', 'mendatang', $parent->id);
        $this->roomReservation($b, '15:00', '16:00', 'mendatang', $parent->id);
        $this->actingAs($this->user)->get(route('dashboard'))->assertOk(); // pemanasan: sinkronisasi status
        $few = $this->queryCount(fn () => $this->actingAs($this->user)->get(route('dashboard'))->assertOk());

        foreach (range(1, 25) as $i) {
            $parent = $this->roomReservation($combined, '13:00', '14:00');
            $this->roomReservation($a, '13:00', '14:00', 'mendatang', $parent->id);
            $this->roomReservation($b, '13:00', '14:00', 'mendatang', $parent->id);
        }
        $many = $this->queryCount(fn () => $this->actingAs($this->user)->get(route('dashboard'))->assertOk());

        $this->assertSame($few, $many);
    }

    public function test_dashboard_hanya_membangun_detail_untuk_halaman_yang_tampil(): void
    {
        $a = $this->room('Ruang A');
        foreach (range(1, 25) as $i) {
            $this->roomReservation($a, '11:00', '12:00', 'selesai');
        }

        $paginator = $this->actingAs($this->user)->get(route('dashboard'))->assertOk()->viewData('reservations');

        $this->assertSame(25, $paginator->total());
        $this->assertCount(10, $paginator->items());
        $this->assertArrayHasKey('detail', $paginator->items()[0]);
    }
}
