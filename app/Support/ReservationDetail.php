<?php

namespace App\Support;

use App\Models\AttendanceForm;
use App\Models\RoomReservation;
use App\Models\ZoomReservation;

/**
 * Membentuk data untuk modal detail reservasi bersama
 * (resources/views/partials/reservation-detail-modal.blade.php).
 *
 * Dashboard dan Semua Pemesanan (Admin) sama-sama memanggil class ini, jadi isi
 * modal detail (Ruang Rapat, Breakout Room Zoom, Form Kehadiran) selalu sama.
 * Perbedaan antar halaman diatur lewat $opts:
 *
 *  - 'pemesan' => true            tampilkan kartu "Informasi Pemesan"
 *  - 'cancel'  => 'request'       tombol "Ajukan Pembatalan" (user, hanya status mendatang)
 *              => 'admin'         tombol "Batalkan Reservasi" (admin, selain selesai/dibatalkan)
 *              => null            tanpa tombol pembatalan
 */
class ReservationDetail
{
    public static function room(RoomReservation $item, string $kode, array $opts = []): array
    {
        return array_merge(self::base($kode, 'room', 'Ruang Rapat', $item, $item->status, $opts), [
            'ruang' => $item->room->name ?? '-',
            'lantai' => $item->room->floor ?? null,
            'kapasitas' => $item->room->capacity_label ?? null,
            'ruang_gabungan' => $item->childReservations
                ->map(fn ($c) => $c->room->name ?? '-')
                ->implode(', ') ?: null,
            'tanggal' => $item->tanggal->translatedFormat('d F Y'),
            'waktu' => $item->jam_range,
            'jumlah_peserta' => $item->jumlah_peserta,
            'konsumsi' => $item->konsumsi,
            'keperluan' => $item->keperluan,
            'nama_pic' => $item->nama_pic,
            'no_telp_pic' => $item->no_telp_pic,
            'divisi_pic' => $item->divisi_pic,
            'cancel' => self::cancel($opts, $item->status, 'room', $item->id),
        ]);
    }

    public static function zoom(ZoomReservation $item, string $kode, array $opts = []): array
    {
        return array_merge(self::base($kode, 'zoom', 'Breakout Room Zoom', $item, $item->status, $opts), [
            'ruang' => 'Ruang '.$item->room_number,
            'tanggal' => $item->tanggal->translatedFormat('d F Y'),
            'waktu' => $item->jam_range,
            'keperluan' => $item->nama_agenda,
            'nama_pic' => $item->nama_pic,
            'no_telp_pic' => $item->no_telp_pic,
            'divisi_pic' => $item->divisi_pic,
            'broadcast' => $item->broadcast_text,
            'zoom_link' => ZoomReservation::MEETING_URL,
            'cancel' => self::cancel($opts, $item->status, 'zoom', $item->id),
        ]);
    }

    public static function form(AttendanceForm $item, string $kode, array $opts = []): array
    {
        // Form tidak punya kolom status: aktif = mendatang, kedaluwarsa = selesai.
        $status = $item->isExpired() ? 'selesai' : 'mendatang';

        return array_merge(self::base($kode, 'form', 'Form Kehadiran', $item, $status, $opts), [
            'nama_pic' => $item->pic,
            'divisi_pic' => $item->divisi_pic,
            'form' => [
                'judul' => $item->judul,
                'tempat' => $item->tempat,
                'rapat' => $item->rapat_pertemuan,
                'expires_label' => $item->expires_at
                    ? $item->expires_at->translatedFormat('d F Y, H:i').' WIB'
                    : '-',
                'link' => route('attendance.public', $item->uuid),
            ],
        ]);
    }

    /** Kunci yang selalu ada, supaya modal tidak perlu mengecek satu per satu. */
    private static function base(string $kode, string $jenis, string $jenisLabel, $item, string $status, array $opts): array
    {
        return [
            'kode' => $kode,
            'jenis' => $jenis,
            'jenis_label' => $jenisLabel,
            'status' => $status,
            'created_at' => $item->created_at->translatedFormat('d F Y, H:i').' WIB',
            'pemesan' => ! empty($opts['pemesan']) ? self::pemesan($item) : null,
            'ruang' => null,
            'lantai' => null,
            'kapasitas' => null,
            'ruang_gabungan' => null,
            'tanggal' => null,
            'waktu' => null,
            'jumlah_peserta' => null,
            'konsumsi' => null,
            'keperluan' => null,
            'nama_pic' => null,
            'no_telp_pic' => null,
            'divisi_pic' => null,
            'broadcast' => null,
            'zoom_link' => null,
            'form' => null,
            'cancel' => null,
        ];
    }

    private static function pemesan($item): array
    {
        $user = $item->user;

        return [
            'name' => $user->name ?? null,
            'nip' => $user->nip ?? null,
            'divisi' => $user->divisi ?? null,
            'jabatan' => $user->jabatan ?? null,
            'email' => $user->email ?? null,
        ];
    }

    private static function cancel(array $opts, string $status, string $jenis, int $rawId): ?array
    {
        $mode = $opts['cancel'] ?? null;

        $allowed = match ($mode) {
            'request' => $status === 'mendatang',
            'admin' => ! in_array($status, ['selesai', 'dibatalkan'], true),
            default => false,
        };

        if (! $allowed) {
            return null;
        }

        return [
            'label' => $mode === 'admin' ? 'Batalkan Reservasi' : 'Ajukan Pembatalan',
            'jenis' => $jenis,
            'raw_id' => $rawId,
        ];
    }
}
