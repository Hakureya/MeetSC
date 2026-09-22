<?php

namespace App\Http\Controllers;

use App\Models\ZoomReservation;
use App\Support\TimeSlots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Halaman menu "Breakout Room Zoom": "Jadwal Reservasi Breakout Room Zoom".
 * Ini menggantikan halaman yang sebelumnya langsung berisi form pemesanan; form itu sendiri
 * dipindah ke ZoomReservationController@index, route "zoom.create".
 *
 *  - Tabel ruangan (jadwal hari ini): satu baris per breakout room (Ruang 1 – Ruang 9), menampilkan
 *    agenda yang sedang/berikutnya berlangsung. Pergantian agenda dalam satu ruangan dihitung di
 *    sisi klien berdasarkan jam real time, jadi controller cukup mengirim SEMUA agenda hari ini.
 *  - Panel "Agenda Hari Ini / Akan Datang": admin melihat agenda semua akun, user biasa hanya
 *    agenda miliknya sendiri.
 */
class ScheduleController extends Controller
{
    /** Status yang masih "memakai" ruangan. */
    private const ACTIVE_STATUSES = ['mendatang', 'menunggu_pembatalan'];

    /** Batas jumlah kartu agenda di panel kanan. */
    private const MAX_AGENDAS = 100;

    public function index(Request $request)
    {
        return view('schedule.index', [
            'payload' => $this->payload($request),
        ]);
    }

    /**
     * Data terbaru untuk polling dari halaman (dan saat pergantian hari tengah malam).
     */
    public function data(Request $request): JsonResponse
    {
        return response()
            ->json($this->payload($request))
            ->header('Cache-Control', 'no-store');
    }

    private function payload(Request $request): array
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();
        $now = now();
        $today = $now->toDateString();

        $slots = TimeSlots::all();

        return [
            // Waktu server dipakai klien untuk menghitung selisih jam, supaya tampilan
            // tidak bergantung pada jam perangkat yang bisa saja meleset.
            'now_ms' => $now->getTimestampMs(),
            'timezone' => config('app.timezone'),
            'today' => $today,
            'is_admin' => $isAdmin,
            'open' => $slots[0]['start'],
            'close' => $slots[array_key_last($slots)]['end'],
            'rooms' => $this->roomsPayload($today),
            'agendas' => $this->agendasPayload($user, $isAdmin, $today),
        ];
    }

    /**
     * Ruang 1 – Ruang 9 beserta daftar agenda HARI INI (urut jam mulai).
     * Tabel memuat booking semua akun supaya status "Tersedia" selalu benar.
     */
    private function roomsPayload(string $today): array
    {
        $byRoom = ZoomReservation::whereDate('tanggal', $today)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('room_number');

        return collect(range(1, ZoomReservation::ROOM_COUNT))->map(function (int $n) use ($byRoom) {
            return [
                'id' => $n,
                'name' => 'Ruang '.$n,
                'entries' => ($byRoom[$n] ?? collect())->map(fn (ZoomReservation $r) => [
                    'rid' => 'ZM-'.$r->id,
                    'agenda' => $r->nama_agenda,
                    'pic' => $r->nama_pic,
                    'status' => $r->status,
                    'start' => substr($r->jam_mulai, 0, 5),
                    'end' => substr($r->jam_selesai, 0, 5),
                ])->values()->all(),
            ];
        })->all();
    }

    /**
     * Agenda hari ini dan yang akan datang untuk panel kanan.
     * Admin: semua akun. User biasa: hanya miliknya sendiri.
     */
    private function agendasPayload($user, bool $isAdmin, string $today): array
    {
        $query = ZoomReservation::with('user')
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereDate('tanggal', '>=', $today)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai');

        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        return $query->limit(self::MAX_AGENDAS)->get()->map(function (ZoomReservation $r) use ($user) {
            $isOwner = $r->user_id === $user->id;

            return [
                'rid' => 'ZM-'.$r->id,
                'date' => $r->tanggal->toDateString(),
                'start' => substr($r->jam_mulai, 0, 5),
                'end' => substr($r->jam_selesai, 0, 5),
                'room' => 'Ruang '.$r->room_number,
                'agenda' => $r->nama_agenda,
                'status' => $r->status,

                'pic' => $r->nama_pic,
                'phone' => $r->no_telp_pic,
                'divisi' => $r->divisi_pic,
                'booker' => $r->user->name ?? null,
                'created_at' => $r->created_at->copy()->locale('id')->translatedFormat('d F Y, H:i').' WIB',

                // Tautan di modal Detail. Link Zoom hanya ikut terkirim untuk agenda yang memang
                // boleh dilihat pengguna ini (milik sendiri, atau semua untuk admin).
                'zoom_url' => ZoomReservation::MEETING_URL,
                // Teks broadcast/undangan rapat siap salin, disamakan dengan Dashboard & Semua Pemesanan.
                'broadcast' => $r->broadcast_text,
                // Pemilik → Dashboard (Reservasi Saya). Admin yang membuka agenda milik akun lain
                // → halaman Semua Pemesanan, karena Dashboard hanya memuat reservasi milik sendiri.
                'dashboard_url' => $isOwner
                    ? route('dashboard', ['search' => 'ZM-'.$r->id])
                    : route('reservations.index', ['search' => 'ZM'.str_pad($r->id, 3, '0', STR_PAD_LEFT)]),
                'dashboard_label' => $isOwner ? 'Lihat di Dashboard' : 'Lihat di Semua Pemesanan',
            ];
        })->all();
    }
}
