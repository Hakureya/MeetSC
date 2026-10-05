<?php

namespace App\Http\Controllers;

use App\Events\ZoomRoomsUpdated;
use App\Models\AttendanceForm;
use App\Models\RoomReservation;
use App\Models\ZoomReservation;
use App\Support\ReservationDetail;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Ambil Data (satu query per tabel)
        |--------------------------------------------------------------------------
        | Reservasi turunan ruang gabungan dikecualikan supaya satu pemesanan gabungan
        | tetap tampil sebagai satu baris. Relasi yang berat (childReservations) TIDAK
        | di-eager-load di sini; hanya dimuat untuk baris yang benar-benar ditampilkan.
        */

        $roomReservations = $user->roomReservations()
            ->with('room')
            ->where('auto_generated', false)
            ->get();

        $zoomReservations = $user->zoomReservations()->get();

        $attendanceForms = $user->attendanceForms()->get();

        /*
        |--------------------------------------------------------------------------
        | Baris Ringan
        |--------------------------------------------------------------------------
        | Hanya field yang dibutuhkan untuk filter, pengurutan, dan pagination, plus
        | referensi model. Array lengkap (modal detail, teks broadcast, dsb.) baru
        | dibangun setelah pagination, hanya untuk baris yang tampil.
        */

        $rows = collect();

        foreach ($roomReservations as $r) {
            $rows->push([
                'id' => 'RM-' . $r->id,
                'jenis' => 'room',
                'ruangan' => $r->room->name ?? '-',
                'keperluan' => $r->keperluan ?? '-',
                'status' => $r->status,
                'jam_mulai' => $r->jam_mulai,
                'tanggal_raw' => $r->tanggal,
                'created_at' => $r->created_at,
                'model' => $r,
            ]);
        }

        foreach ($zoomReservations as $r) {
            $rows->push([
                'id' => 'ZM-' . $r->id,
                'jenis' => 'zoom',
                'ruangan' => 'Ruang ' . $r->room_number,
                'keperluan' => $r->nama_agenda ?? '-',
                'status' => $r->status,
                'jam_mulai' => $r->jam_mulai,
                'tanggal_raw' => $r->tanggal,
                'created_at' => $r->created_at,
                'model' => $r,
            ]);
        }

        foreach ($attendanceForms as $f) {
            $rows->push([
                'id' => 'FM-' . $f->id,
                'jenis' => 'form',
                'ruangan' => $f->tempat ?? '-',
                // Kolom keperluan untuk form diisi dari atribut rapat/agenda form.
                'keperluan' => $f->rapat_pertemuan ?? '-',
                'status' => $f->isExpired() ? 'selesai' : 'mendatang',
                'jam_mulai' => null,
                'tanggal_raw' => $f->created_at,
                'created_at' => $f->created_at,
                'model' => $f,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Reservasi Hari Ini (kartu di atas)
        |--------------------------------------------------------------------------
        | Diambil dari data yang sudah dimuat -- tidak perlu query tambahan.
        */

        $todayRows = $rows
            ->filter(fn ($r) => in_array($r['jenis'], ['room', 'zoom'], true)
                && $r['status'] === 'mendatang'
                && $r['tanggal_raw']->toDateString() === $today)
            ->sortBy('jam_mulai')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Filter Pencarian
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $rows = $rows->filter(function ($r) use ($search) {
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
            $rows = $rows->where('jenis', $request->jenis);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $rows = $rows->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Tanggal
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tanggal_mulai')) {
            $rows = $rows->filter(function ($r) use ($request) {
                return $r['tanggal_raw']->format('Y-m-d')
                    >= $request->tanggal_mulai;
            });
        }

        if ($request->filled('tanggal_akhir')) {
            $rows = $rows->filter(function ($r) use ($request) {
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

        $rows = match ($sort) {
            'terlama' => $rows->sortBy('created_at'),

            'tanggal_asc' => $rows->sortBy('tanggal_raw'),

            'tanggal_desc' => $rows->sortByDesc('tanggal_raw'),

            default => $rows->sortByDesc('created_at'),
        };

        $rows = $rows->values();

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = 10;

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $pageRows = $rows->forPage($currentPage, $perPage);

        // Muat ruang komponen (ruang gabungan) sekali saja untuk SEMUA baris yang akan
        // ditampilkan: kartu hari ini + halaman tabel saat ini.
        (new EloquentCollection(
            $todayRows->merge($pageRows)
                ->where('jenis', 'room')
                ->pluck('model')
                ->unique('id')
                ->all()
        ))->load('childReservations.room');

        /*
        |--------------------------------------------------------------------------
        | Bentuk Array Lengkap Hanya untuk Baris yang Tampil
        |--------------------------------------------------------------------------
        */

        $todayReservations = $todayRows
            ->map(fn ($row) => $row['jenis'] === 'room'
                ? $this->todayRoomCard($row['model'])
                : $this->todayZoomCard($row['model']))
            ->values();

        $paginatedReservations = new LengthAwarePaginator(
            $pageRows->map(fn ($row) => match ($row['jenis']) {
                'room' => $this->roomRow($row['model']),
                'zoom' => $this->zoomRow($row['model']),
                default => $this->formRow($row['model']),
            }),
            $rows->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('dashboard', [
            'todayReservations' => $todayReservations,
            'reservations' => $paginatedReservations,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Pembentuk Array Tampilan
    |--------------------------------------------------------------------------
    */

    private function todayRoomCard(RoomReservation $r): array
    {
        return [
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
            // Data modal detail bersama (sama dengan halaman Semua Pemesanan).
            'detail' => ReservationDetail::room($r, 'RM-' . $r->id),
            'broadcast' => null,
            'zoom_link' => null,
        ];
    }

    private function todayZoomCard(ZoomReservation $r): array
    {
        return [
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
            // Data modal detail bersama (sama dengan halaman Semua Pemesanan).
            'detail' => ReservationDetail::zoom($r, 'ZM-' . $r->id),
            'broadcast' => $r->broadcast_text,
            'zoom_link' => ZoomReservation::ZOOM_LINK,
        ];
    }

    private function roomRow(RoomReservation $r): array
    {
        return [
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
            // Data modal detail bersama (sama dengan halaman Semua Pemesanan).
            'detail' => ReservationDetail::room($r, 'RM-' . $r->id, ['cancel' => 'request']),
            'jumlah_peserta' => $r->jumlah_peserta,
            'konsumsi' => $r->konsumsi,
            'nama_pic' => $r->nama_pic,
            'no_telp_pic' => $r->no_telp_pic,
            'divisi_pic' => $r->divisi_pic,
            'can_cancel' => true,
            'broadcast' => null,
            'zoom_link' => null,
        ];
    }

    private function zoomRow(ZoomReservation $r): array
    {
        return [
            'id' => 'ZM-' . $r->id,
            'raw_id' => $r->id,
            'jenis' => 'zoom',
            'jenis_label' => 'Breakout Room Zoom',
            'ruangan' => 'Ruang ' . $r->room_number,
            'keperluan' => $r->nama_agenda ?? '-',
            'tanggal' => $r->tanggal->format('d/m/Y'),
            'tanggal_lengkap' => $r->tanggal->translatedFormat('d F Y'),
            'tanggal_raw' => $r->tanggal,
            'waktu' => $r->jam_range,
            'status' => $r->status,
            'created_at' => $r->created_at,
            'created_at_label' => $r->created_at->translatedFormat('d F Y, H:i') . ' WIB',
            // Data modal detail bersama (sama dengan halaman Semua Pemesanan).
            'detail' => ReservationDetail::zoom($r, 'ZM-' . $r->id, ['cancel' => 'request']),
            'jumlah_peserta' => null,
            'konsumsi' => null,
            'nama_pic' => $r->nama_pic,
            'no_telp_pic' => $r->no_telp_pic,
            'divisi_pic' => $r->divisi_pic,
            'can_cancel' => true,
            'broadcast' => $r->broadcast_text,
            'zoom_link' => ZoomReservation::ZOOM_LINK,
        ];
    }

    private function formRow(AttendanceForm $f): array
    {
        return [
            'id' => 'FM-' . $f->id,
            'raw_id' => $f->id,
            'jenis' => 'form',
            'jenis_label' => 'Form Kehadiran',
            'ruangan' => $f->tempat ?? '-',
            // Kolom keperluan untuk form diisi dari atribut rapat/agenda form.
            'keperluan' => $f->rapat_pertemuan ?? '-',
            'tanggal' => $f->created_at->format('d/m/Y'),
            'tanggal_lengkap' => $f->created_at->translatedFormat('d F Y'),
            'tanggal_raw' => $f->created_at,
            'waktu' => $f->expires_at ? 'Kedaluwarsa ' . $f->expires_at->format('d/m/Y H:i') : '-',
            'status' => $f->isExpired() ? 'selesai' : 'mendatang',
            'created_at' => $f->created_at,
            'created_at_label' => $f->created_at->translatedFormat('d F Y, H:i') . ' WIB',
            // Data modal detail bersama (sama dengan halaman Semua Pemesanan).
            'detail' => ReservationDetail::form($f, 'FM-' . $f->id),
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
        ];
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

        // Reservasi Zoom yang diajukan pembatalan tetap "memakai" ruangan sampai
        // disetujui Admin, tapi statusnya berubah -- siarkan supaya tabel jadwal ikut update.
        if ($request->type === 'zoom') {
            broadcast(new ZoomRoomsUpdated($reservation->id, $reservation->tanggal->toDateString()));
        }

        return back()->with(
            'success',
            'Permintaan pembatalan berhasil dikirim ke Admin.'
        );
    }
}