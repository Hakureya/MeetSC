<?php

namespace App\Http\Controllers\Admin;

use App\Events\ZoomRoomsUpdated;
use App\Http\Controllers\Controller;
use App\Models\AttendanceForm;
use App\Models\RoomReservation;
use App\Models\ZoomReservation;
use Illuminate\Http\Request;

class ReservationManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $jenis = $request->get('jenis');
        $status = $request->get('status');
        $tanggalMulai = $request->get('tanggal_mulai');
        $tanggalAkhir = $request->get('tanggal_akhir');
        $sort = $request->get('sort', 'terbaru');

        // Reservasi turunan ruang gabungan dikecualikan agar riwayat tetap dihitung satu.
        $roomQuery = RoomReservation::with(['user', 'room', 'childReservations.room'])->where('auto_generated', false);
        $zoomQuery = ZoomReservation::with('user');
        $formQuery = AttendanceForm::with('user');

        // Status "mendatang"/"selesai"/"menunggu_pembatalan"/"dibatalkan" hanya
        // berlaku untuk reservasi ruang & zoom. Form kehadiran hanya mengenal
        // "mendatang" (masih aktif) dan "selesai" (sudah kedaluwarsa), jadi
        // filter status untuk form ditangani terpisah setelah data diambil.
        if ($status) {
            $roomQuery->where('status', $status);
            $zoomQuery->where('status', $status);
        }

        $rooms = ($jenis && $jenis !== 'Ruang') ? collect() : $roomQuery->get()->map(function ($item) {
            return [
                'id' => 'BK' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'raw_id' => $item->id,
                'pemesan' => $item->user->name,
                'user' => $item->user,
                'jenis' => 'Ruang',
                'ruang' => $item->room->name,
                'tanggal' => $item->tanggal->format('d M'),
                'tanggal_lengkap' => $item->tanggal->translatedFormat('d F Y'),
                'tanggal_raw' => $item->tanggal,
                'waktu' => $item->jam_range,
                'keperluan' => $item->keperluan,
                'status' => $item->status,
                'lantai' => $item->room->floor ?? null,
                'kapasitas' => $item->room->capacity_label ?? null,
                'jumlah_peserta' => $item->jumlah_peserta,
                'konsumsi' => $item->konsumsi,
                'nama_pic' => $item->nama_pic,
                'no_telp_pic' => $item->no_telp_pic,
                'divisi_pic' => $item->divisi_pic,
                'email_pemesan' => $item->user->email ?? null,
                'divisi_pemesan' => $item->user->divisi ?? null,
                // Ruang gabungan: daftar ruang komponen yang ikut terpakai otomatis.
                'ruang_gabungan' => $item->childReservations->map(fn ($c) => $c->room->name ?? '-')->implode(', ') ?: null,
                'created_at' => $item->created_at,
                'dibuat' => $item->created_at->translatedFormat('d F Y, H:i') . ' WIB',
                'can_cancel' => true,
                'broadcast' => null,
                'zoom_link' => null,
                'raw_item' => $item,
            ];
        });

        $zooms = ($jenis && $jenis !== 'Zoom') ? collect() : $zoomQuery->get()->map(function ($item) {
            return [
                'id' => 'ZM' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'raw_id' => $item->id,
                'pemesan' => $item->user->name,
                'user' => $item->user,
                'jenis' => 'Zoom',
                'ruang' => 'Ruang ' . $item->room_number,
                'tanggal' => $item->tanggal->format('d M'),
                'tanggal_lengkap' => $item->tanggal->translatedFormat('d F Y'),
                'tanggal_raw' => $item->tanggal,
                'waktu' => $item->jam_range,
                'keperluan' => $item->nama_agenda,
                'status' => $item->status,
                'lantai' => null,
                'kapasitas' => null,
                'jumlah_peserta' => null,
                'konsumsi' => null,
                'nama_pic' => $item->nama_pic,
                'no_telp_pic' => $item->no_telp_pic,
                'divisi_pic' => $item->divisi_pic,
                'email_pemesan' => $item->user->email ?? null,
                'divisi_pemesan' => $item->user->divisi ?? null,
                'ruang_gabungan' => null,
                'created_at' => $item->created_at,
                'dibuat' => $item->created_at->translatedFormat('d F Y, H:i') . ' WIB',
                'can_cancel' => true,
                'broadcast' => $item->broadcast_text,
                'zoom_link' => \App\Models\ZoomReservation::MEETING_URL,
                'raw_item' => $item,
            ];
        });

        // Riwayat form kehadiran turut dimasukkan ke Semua Pemesanan. Form
        // tidak bisa dibatalkan sehingga can_cancel selalu false.
        $forms = ($jenis && $jenis !== 'Form') ? collect() : $formQuery->get()->map(function ($item) {
            $formStatus = $item->isExpired() ? 'selesai' : 'mendatang';

            return [
                'id' => 'FM' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'raw_id' => $item->id,
                'pemesan' => $item->user->name ?? '-',
                'user' => $item->user,
                'jenis' => 'Form',
                'ruang' => $item->tempat,
                'tanggal' => $item->created_at->format('d M'),
                'tanggal_lengkap' => $item->created_at->translatedFormat('d F Y'),
                'tanggal_raw' => $item->created_at,
                'waktu' => $item->expires_at ? 'Kedaluwarsa ' . $item->expires_at->format('d/m/Y H:i') : '-',
                // Kolom keperluan untuk form diisi dari atribut rapat/agenda form.
                'keperluan' => $item->rapat_pertemuan ?? '-',
                'status' => $formStatus,
                'lantai' => null,
                'kapasitas' => null,
                'jumlah_peserta' => null,
                'konsumsi' => null,
                'nama_pic' => $item->pic,
                'no_telp_pic' => null,
                'divisi_pic' => $item->divisi_pic,
                'email_pemesan' => $item->user->email ?? null,
                'divisi_pemesan' => $item->user->divisi ?? null,
                'ruang_gabungan' => null,
                'created_at' => $item->created_at,
                'dibuat' => $item->created_at->translatedFormat('d F Y, H:i') . ' WIB',
                'can_cancel' => false,
                'broadcast' => null,
                'zoom_link' => null,
                'raw_item' => $item,
            ];
        })->when($status, fn ($collection) => $collection->where('status', $status));

        $reservations = $rooms->concat($zooms)->concat($forms);

        if ($search) {
            $reservations = $reservations->filter(function ($r) use ($search) {
                $search = strtolower($search);

                return str_contains(strtolower($r['id']), $search) ||
                       str_contains(strtolower($r['pemesan']), $search) ||
                       str_contains(strtolower($r['ruang']), $search) ||
                       str_contains(strtolower($r['keperluan'] ?? ''), $search);
            });
        }

        // Filter rentang tanggal, disamakan dengan filter di Dashboard.
        if ($tanggalMulai) {
            $reservations = $reservations->filter(
                fn ($r) => $r['tanggal_raw']->format('Y-m-d') >= $tanggalMulai
            );
        }

        if ($tanggalAkhir) {
            $reservations = $reservations->filter(
                fn ($r) => $r['tanggal_raw']->format('Y-m-d') <= $tanggalAkhir
            );
        }

        // Pengurutan, disamakan dengan pilihan urutkan di Dashboard.
        $reservations = match ($sort) {
            'terlama' => $reservations->sortBy('created_at'),
            'tanggal_asc' => $reservations->sortBy('tanggal_raw'),
            'tanggal_desc' => $reservations->sortByDesc('tanggal_raw'),
            default => $reservations->sortByDesc('created_at'),
        };

        $reservations = $reservations->values();

        // Maksimal 10 riwayat per halaman, selebihnya memakai pagination.
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();

        $reservations = new \Illuminate\Pagination\LengthAwarePaginator(
            $reservations->forPage($currentPage, $perPage),
            $reservations->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.reservations.index', compact('reservations'));
    }

    public function cancel(Request $request, $type, $id)
    {
        if ($type === 'Ruang') {
            $res = RoomReservation::findOrFail($id);
            // Ruang gabungan: ikut batalkan reservasi turunan di ruang komponennya.
            $res->childReservations()->update(['status' => 'dibatalkan']);
        } elseif ($type === 'Zoom') {
            $res = ZoomReservation::findOrFail($id);
        } else {
            // Form kehadiran tidak memiliki mekanisme pembatalan.
            return back()->withErrors([
                'cancel' => 'Form kehadiran tidak dapat dibatalkan.',
            ]);
        }

        // Reservasi yang sudah "selesai" tidak dapat dibatalkan.
        if ($res->status === 'selesai') {
            return back()->withErrors([
                'cancel' => 'Reservasi yang sudah selesai tidak dapat dibatalkan.',
            ]);
        }

        $res->update(['status' => 'dibatalkan']);

        // Ruangan yang dibatalkan Admin jadi kosong lagi -- siarkan supaya tabel
        // jadwal breakout room Zoom ter-update seketika untuk pengguna yang membukanya.
        if ($type === 'Zoom') {
            broadcast(new ZoomRoomsUpdated($res->id, $res->tanggal->toDateString()));
        }

        return back()->with('success', 'Reservasi berhasil dibatalkan oleh Admin.');
    }
}