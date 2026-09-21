<?php

namespace App\Http\Controllers;

use App\Models\AttendanceForm;
use App\Models\RoomReservation;
use App\Models\ZoomReservation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Reservasi Saat Ini / Mendatang
        |--------------------------------------------------------------------------
        */

        $today = now()->toDateString();

        // Reservasi ruang untuk HARI INI saja. Reservasi turunan ruang gabungan
        // dikecualikan supaya satu pemesanan gabungan tetap tampil sebagai satu kartu.
        $todayRoomReservations = $user->roomReservations()
            ->with('room')
            ->where('status', 'mendatang')
            ->whereDate('tanggal', $today)
            ->where('auto_generated', false)
            ->orderBy('jam_mulai')
            ->get();

        $todayZoomReservations = $user->zoomReservations()
            ->where('status', 'mendatang')
            ->whereDate('tanggal', $today)
            ->orderBy('jam_mulai')
            ->get();

        // Gabungkan keduanya menjadi satu daftar kartu, diurutkan berdasarkan jam mulai.
        $todayReservations = collect();

        foreach ($todayRoomReservations as $r) {
            $todayReservations->push([
                'id' => 'RM-' . $r->id,
                'jenis' => 'room',
                'jenis_label' => 'Ruang Rapat',
                'ruangan' => $r->room->name ?? '-',
                'keperluan' => $r->keperluan ?? '-',
                'tanggal_lengkap' => $r->tanggal->translatedFormat('d F Y'),
                'jam_mulai' => $r->jam_mulai,
                'waktu' => $r->jam_range,
                'status' => $r->status,
                'jumlah_peserta' => $r->jumlah_peserta,
                'konsumsi' => $r->konsumsi,
                'nama_pic' => $r->nama_pic,
                'no_telp_pic' => $r->no_telp_pic,
                'divisi_pic' => $r->divisi_pic,
                'created_at_label' => $r->created_at->translatedFormat('d F Y, H:i') . ' WIB',
            ]);
        }

        foreach ($todayZoomReservations as $r) {
            $todayReservations->push([
                'id' => 'ZM-' . $r->id,
                'jenis' => 'zoom',
                'jenis_label' => 'Breakout Room Zoom',
                'ruangan' => 'Zoom Ruang ' . $r->room_number,
                'keperluan' => $r->nama_agenda ?? '-',
                'tanggal_lengkap' => $r->tanggal->translatedFormat('d F Y'),
                'jam_mulai' => $r->jam_mulai,
                'waktu' => $r->jam_range,
                'status' => $r->status,
                'jumlah_peserta' => null,
                'konsumsi' => null,
                'nama_pic' => $r->nama_pic,
                'no_telp_pic' => $r->no_telp_pic,
                'divisi_pic' => $r->divisi_pic,
                'created_at_label' => $r->created_at->translatedFormat('d F Y, H:i') . ' WIB',
            ]);
        }

        $todayReservations = $todayReservations->sortBy('jam_mulai')->values();

        /*
        |--------------------------------------------------------------------------
        | Aktivitas Saya
        |--------------------------------------------------------------------------
        */

        $recentRoomReservations = $user->roomReservations()
            ->with('room')
            ->latest()
            ->take(5)
            ->get();

        $recentZoomReservations = $user->zoomReservations()
            ->latest()
            ->take(5)
            ->get();

        $recentAttendanceForms = $user->attendanceForms()
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Semua Reservasi Saya
        |--------------------------------------------------------------------------
        */

        $roomReservations = $user->roomReservations()
            ->with('room')
            ->where('auto_generated', false)
            ->get();

        $zoomReservations = $user->zoomReservations()
            ->get();

        $attendanceForms = $user->attendanceForms()
            ->get();

        $reservations = collect();

        /*
        |--------------------------------------------------------------------------
        | Format Reservasi Ruang Meeting
        |--------------------------------------------------------------------------
        */

        foreach ($roomReservations as $r) {
            $reservations->push([
                'id' => 'RM-' . $r->id,
                'raw_id' => $r->id,
                'jenis' => 'room',
                'jenis_label' => 'Ruang Rapat',
                'ruangan' => $r->room->name ?? '-',
                'keperluan' => $r->keperluan ?? '-',
                'tanggal' => $r->tanggal->format('d/m/Y'),
                'tanggal_lengkap' => $r->tanggal->translatedFormat('d F Y'),
                'tanggal_raw' => $r->tanggal,
                'waktu' => $r->jam_range,
                'status' => $r->status,
                'created_at' => $r->created_at,
                'created_at_label' => $r->created_at->translatedFormat('d F Y, H:i') . ' WIB',
                'jumlah_peserta' => $r->jumlah_peserta,
                'konsumsi' => $r->konsumsi,
                'nama_pic' => $r->nama_pic,
                'no_telp_pic' => $r->no_telp_pic,
                'divisi_pic' => $r->divisi_pic,
                'can_cancel' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Format Reservasi Zoom
        |--------------------------------------------------------------------------
        */

        foreach ($zoomReservations as $r) {
            $reservations->push([
                'id' => 'ZM-' . $r->id,
                'raw_id' => $r->id,
                'jenis' => 'zoom',
                'jenis_label' => 'Breakout Room Zoom',
                'ruangan' => 'Zoom Meeting',
                'keperluan' => $r->nama_agenda ?? '-',
                'tanggal' => $r->tanggal->format('d/m/Y'),
                'tanggal_lengkap' => $r->tanggal->translatedFormat('d F Y'),
                'tanggal_raw' => $r->tanggal,
                'waktu' => $r->jam_range,
                'status' => $r->status,
                'created_at' => $r->created_at,
                'created_at_label' => $r->created_at->translatedFormat('d F Y, H:i') . ' WIB',
                'jumlah_peserta' => null,
                'konsumsi' => null,
                'nama_pic' => $r->nama_pic,
                'no_telp_pic' => $r->no_telp_pic,
                'divisi_pic' => $r->divisi_pic,
                'can_cancel' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Format Riwayat Form Kehadiran
        |--------------------------------------------------------------------------
        */

        foreach ($attendanceForms as $f) {
            $reservations->push([
                'id' => 'FM-' . $f->id,
                'raw_id' => $f->id,
                'jenis' => 'form',
                'jenis_label' => 'Form Kehadiran',
                'ruangan' => $f->judul,
                // Kolom keperluan untuk form diisi dari atribut rapat/agenda form.
                'keperluan' => $f->rapat_pertemuan ?? '-',
                'tanggal' => $f->created_at->format('d/m/Y'),
                'tanggal_lengkap' => $f->created_at->translatedFormat('d F Y'),
                'tanggal_raw' => $f->created_at,
                'waktu' => $f->expires_at ? 'Kedaluwarsa ' . $f->expires_at->format('d/m/Y H:i') : '-',
                'status' => $f->isExpired() ? 'selesai' : 'mendatang',
                'created_at' => $f->created_at,
                'created_at_label' => $f->created_at->translatedFormat('d F Y, H:i') . ' WIB',
                'jumlah_peserta' => null,
                'konsumsi' => null,
                'nama_pic' => null,
                'no_telp_pic' => null,
                'divisi_pic' => $f->divisi_pic,
                'can_cancel' => false,
                'detail_url' => route('attendance.show', $f->id),

                // Detail pembuatan form, ditampilkan di modal "Lihat Detail"
                // pada dashboard (bukan navigasi ke halaman respons).
                'form_pic' => $f->pic,
                'form_divisi_pic' => $f->divisi_pic,
                'form_tempat' => $f->tempat,
                'form_rapat' => $f->rapat_pertemuan,
                'form_expires_label' => $f->expires_at ? $f->expires_at->translatedFormat('d F Y, H:i') . ' WIB' : '-',
                'form_link' => route('attendance.public', $f->uuid),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Pencarian
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $reservations = $reservations->filter(function ($r) use ($search) {
                return str_contains(strtolower($r['id']), $search)
                    || str_contains(strtolower($r['ruangan']), $search)
                    || str_contains(strtolower($r['keperluan']), $search);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Jenis Reservasi
        |--------------------------------------------------------------------------
        */

        if ($request->filled('jenis')) {
            $reservations = $reservations->where(
                'jenis',
                $request->jenis
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $reservations = $reservations->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Tanggal
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tanggal_mulai')) {
            $reservations = $reservations->filter(function ($r) use ($request) {
                return $r['tanggal_raw']->format('Y-m-d')
                    >= $request->tanggal_mulai;
            });
        }

        if ($request->filled('tanggal_akhir')) {
            $reservations = $reservations->filter(function ($r) use ($request) {
                return $r['tanggal_raw']->format('Y-m-d')
                    <= $request->tanggal_akhir;
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Pengurutan
        |--------------------------------------------------------------------------
        */

        $sort = $request->get('sort', 'terbaru');

        $reservations = match ($sort) {
            'terlama' => $reservations->sortBy('created_at'),

            'tanggal_asc' => $reservations->sortBy('tanggal_raw'),

            'tanggal_desc' => $reservations->sortByDesc('tanggal_raw'),

            default => $reservations->sortByDesc('created_at'),
        };

        $reservations = $reservations->values();

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = 10;

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $paginatedReservations = new LengthAwarePaginator(
            $reservations->forPage($currentPage, $perPage),
            $reservations->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Kirim Data ke Dashboard
        |--------------------------------------------------------------------------
        */

        return view('dashboard', [
            'todayReservations' => $todayReservations,

            'recentRoomReservations' => $recentRoomReservations,
            'recentZoomReservations' => $recentZoomReservations,
            'recentAttendanceForms' => $recentAttendanceForms,

            'reservations' => $paginatedReservations,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Ajukan Pembatalan Reservasi
    |--------------------------------------------------------------------------
    */

    public function requestCancel(Request $request)
    {
        $request->validate([
            'type' => 'required|in:room,zoom',
            'id' => 'required|integer',
        ]);

        if ($request->type === 'room') {
            $reservation = RoomReservation::where(
                'user_id',
                auth()->id()
            )->findOrFail($request->id);
        } else {
            $reservation = ZoomReservation::where(
                'user_id',
                auth()->id()
            )->findOrFail($request->id);
        }

        // Hanya reservasi berstatus "mendatang" yang boleh diajukan pembatalan.
        // Reservasi yang sudah "selesai" (jamnya sudah lewat) atau sudah
        // "dibatalkan"/"menunggu_pembatalan" tidak dapat diajukan lagi.
        if ($reservation->status !== 'mendatang') {
            return back()->withErrors([
                'cancel' => 'Reservasi ini sudah '.str_replace('_', ' ', $reservation->status).' dan tidak dapat diajukan pembatalan.',
            ]);
        }

        $reservation->update([
            'status' => 'menunggu_pembatalan',
        ]);

        // Ruang gabungan: ikut ajukan pembatalan pada reservasi turunan di ruang komponennya.
        if ($request->type === 'room' && method_exists($reservation, 'childReservations')) {
            $reservation->childReservations()->update(['status' => 'menunggu_pembatalan']);
        }

        return back()->with(
            'success',
            'Permintaan pembatalan berhasil dikirim ke Admin.'
        );
    }
}