<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disiarkan setiap kali data reservasi breakout room Zoom berubah: dibuat,
 * diajukan pembatalan oleh pengguna, atau dibatalkan oleh Admin.
 *
 * Tujuannya supaya tabel jadwal di halaman "Breakout Room Zoom"
 * (resources/views/schedule/index.blade.php) yang sedang dibuka pengguna lain
 * ikut ter-update seketika lewat WebSocket (Laravel Reverb), tanpa menunggu
 * siklus polling 60 detik yang sudah ada sebagai cadangan.
 *
 * Memakai ShouldBroadcastNow (bukan ShouldBroadcast) supaya event ini disiarkan
 * langsung di request yang sama, tanpa bergantung pada queue worker terpisah
 * yang berjalan terus-menerus.
 *
 * Payload dibuat seminimal mungkin: klien yang menerima event ini cukup memakainya
 * sebagai sinyal untuk memanggil ulang endpoint zoom.data, yang sudah menghitung
 * data sesuai hak akses (admin vs user biasa) di server -- supaya satu sumber
 * kebenaran datanya tetap di server, bukan diduplikasi di payload broadcast.
 */
class ZoomRoomsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $reservationId,
        public string $tanggal,
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('zoom-schedule')];
    }

    public function broadcastAs(): string
    {
        return 'rooms.updated';
    }
}
