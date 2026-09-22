<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ZoomReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $budi;

    private User $siti;

    protected function setUp(): void
    {
        parent::setUp();

        // Kunci "sekarang" ke pagi hari. Middleware SyncReservationStatuses mengubah reservasi
        // yang jamnya sudah lewat menjadi "selesai", jadi tanpa ini hasil test bergantung jam berjalannya test.
        $this->travelTo(now()->setTime(7, 0));

        $this->admin = $this->makeUser('Admin', 'admin@test.id', 'admin');
        $this->budi = $this->makeUser('Budi Santoso', 'budi@test.id');
        $this->siti = $this->makeUser('Siti Rahma', 'siti@test.id');
    }

    private function makeUser(string $name, string $email, string $role = 'user'): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('rahasia'),
            'divisi' => 'Renbis',
            'role' => $role,
        ]);
    }

    private function book(User $user, int $room, string $date, string $start, string $end, string $agenda, array $extra = []): ZoomReservation
    {
        return ZoomReservation::create(array_merge([
            'user_id' => $user->id,
            'nama_agenda' => $agenda,
            'nama_pic' => $user->name,
            'no_telp_pic' => '0812',
            'divisi_pic' => 'Renbis',
            'tanggal' => $date,
            'jam_mulai' => $start,
            'jam_selesai' => $end,
            'room_number' => $room,
            'status' => 'mendatang',
        ], $extra));
    }

    private function fetchSchedule(User $user): array
    {
        return $this->actingAs($user)->getJson(route('zoom.data'))->assertOk()->json();
    }

    private function room(array $data, int $number): array
    {
        return collect($data['rooms'])->firstWhere('id', $number);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('zoom.index'))->assertRedirect(route('login'));
        $this->get(route('zoom.data'))->assertRedirect(route('login'));
    }

    public function test_zoom_menu_now_shows_the_room_schedule(): void
    {
        $this->assertSame('/link-zoom', route('zoom.index', absolute: false));

        $this->actingAs($this->budi)
            ->get(route('zoom.index'))
            ->assertOk()
            ->assertSee('Jadwal Reservasi Breakout Room Zoom')
            ->assertSee('Reset Harian Aktif')
            ->assertSee(route('zoom.create'), false); // tombol "Buat Reservasi"
    }

    public function test_dashboard_page_is_unchanged(): void
    {
        $this->actingAs($this->budi)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Jadwal Reservasi Breakout Room Zoom');
    }

    public function test_booking_form_is_still_reachable(): void
    {
        $this->actingAs($this->budi)
            ->get(route('zoom.create'))
            ->assertOk()
            ->assertSee('Reservasi Breakout Room Zoom');
    }

    public function test_table_lists_zoom_rooms_1_to_9(): void
    {
        $data = $this->fetchSchedule($this->budi);

        $this->assertSame(range(1, 9), collect($data['rooms'])->pluck('id')->all());
        $this->assertSame('Ruang 1', $data['rooms'][0]['name']);
        foreach ($data['rooms'] as $room) {
            $this->assertSame([], $room['entries']);
        }
    }

    public function test_admin_sees_agendas_from_all_accounts(): void
    {
        $today = now()->toDateString();
        $this->book($this->budi, 1, $today, '09:00:00', '10:00:00', 'Agenda Budi');
        $this->book($this->siti, 2, $today, '11:00:00', '12:00:00', 'Agenda Siti');

        $agendas = collect($this->fetchSchedule($this->admin)['agendas'])->pluck('agenda')->all();

        $this->assertEqualsCanonicalizing(['Agenda Budi', 'Agenda Siti'], $agendas);
    }

    public function test_regular_user_only_sees_own_agendas_in_panel(): void
    {
        $today = now()->toDateString();
        $this->book($this->budi, 1, $today, '09:00:00', '10:00:00', 'Agenda Budi');
        $this->book($this->siti, 2, $today, '11:00:00', '12:00:00', 'Agenda Siti');

        $agendas = collect($this->fetchSchedule($this->budi)['agendas'])->pluck('agenda')->all();

        $this->assertSame(['Agenda Budi'], $agendas);
    }

    public function test_table_still_shows_every_booking_so_availability_is_truthful(): void
    {
        $this->book($this->siti, 3, now()->toDateString(), '09:00:00', '10:00:00', 'Agenda Siti');

        $entries = $this->room($this->fetchSchedule($this->budi), 3)['entries'];

        $this->assertSame(['Agenda Siti'], collect($entries)->pluck('agenda')->all());
    }

    public function test_cancelled_and_finished_reservations_are_excluded(): void
    {
        $today = now()->toDateString();
        $this->book($this->budi, 1, $today, '09:00:00', '10:00:00', 'Batal', ['status' => 'dibatalkan']);
        $this->book($this->budi, 1, $today, '10:00:00', '11:00:00', 'Selesai', ['status' => 'selesai']);
        $this->book($this->budi, 1, $today, '13:00:00', '14:00:00', 'Aktif');

        $data = $this->fetchSchedule($this->admin);

        $this->assertSame(['Aktif'], collect($data['agendas'])->pluck('agenda')->all());
        $this->assertSame(['Aktif'], collect($this->room($data, 1)['entries'])->pluck('agenda')->all());
    }

    public function test_past_days_are_not_listed_but_upcoming_days_are(): void
    {
        $this->book($this->budi, 1, now()->subDay()->toDateString(), '09:00:00', '10:00:00', 'Kemarin');
        $this->book($this->budi, 1, now()->addDays(2)->toDateString(), '09:00:00', '10:00:00', 'Lusa');

        $data = $this->fetchSchedule($this->admin);

        $this->assertSame(['Lusa'], collect($data['agendas'])->pluck('agenda')->all());

        // Tabel hanya memuat jadwal HARI INI.
        foreach ($data['rooms'] as $room) {
            $this->assertSame([], $room['entries']);
        }
    }

    public function test_same_room_entries_are_sorted_so_the_second_agenda_follows_the_first(): void
    {
        $today = now()->toDateString();
        $this->book($this->budi, 6, $today, '14:00:00', '16:00:00', 'Kedua');
        $this->book($this->budi, 6, $today, '09:00:00', '10:30:00', 'Pertama');

        $entries = $this->room($this->fetchSchedule($this->admin), 6)['entries'];

        $this->assertSame(['Pertama', 'Kedua'], collect($entries)->pluck('agenda')->all());
        $this->assertSame(['09:00', '10:30'], [$entries[0]['start'], $entries[0]['end']]);
        $this->assertSame('ZM-'.ZoomReservation::where('nama_agenda', 'Pertama')->value('id'), $entries[0]['rid']);
    }

    public function test_detail_links_depend_on_ownership(): void
    {
        $r = $this->book($this->budi, 1, now()->toDateString(), '09:00:00', '10:00:00', 'Agenda');

        // Pemilik → Dashboard (Reservasi Saya) + link Zoom.
        $own = $this->fetchSchedule($this->budi)['agendas'][0];
        $this->assertSame(route('dashboard', ['search' => 'ZM-'.$r->id]), $own['dashboard_url']);
        $this->assertSame('Lihat di Dashboard', $own['dashboard_label']);
        $this->assertSame(ZoomReservation::MEETING_URL, $own['zoom_url']);

        // Admin membuka agenda milik akun lain → diarahkan ke Semua Pemesanan.
        $other = $this->fetchSchedule($this->admin)['agendas'][0];
        $this->assertStringContainsString('/semua-pemesanan', $other['dashboard_url']);
        $this->assertSame('Lihat di Semua Pemesanan', $other['dashboard_label']);
    }

    public function test_zoom_link_is_not_sent_for_other_users_agendas(): void
    {
        $this->book($this->siti, 1, now()->toDateString(), '09:00:00', '10:00:00', 'Agenda Siti');

        $payload = json_encode($this->fetchSchedule($this->budi));

        $this->assertStringNotContainsString('zoom.us', $payload);
    }

    public function test_dashboard_search_link_finds_the_reservation(): void
    {
        $r = $this->book($this->budi, 1, now()->toDateString(), '09:00:00', '10:00:00', 'Agenda Unik Cari');

        $this->actingAs($this->budi)
            ->get(route('dashboard', ['search' => 'ZM-'.$r->id]))
            ->assertOk()
            ->assertSee('Agenda Unik Cari');
    }

    public function test_payload_carries_server_time_for_the_realtime_clock(): void
    {
        $data = $this->fetchSchedule($this->budi);

        $this->assertSame(config('app.timezone'), $data['timezone']);
        $this->assertSame(now()->toDateString(), $data['today']);
        $this->assertEqualsWithDelta(now()->getTimestampMs(), $data['now_ms'], 5000);
    }
}
