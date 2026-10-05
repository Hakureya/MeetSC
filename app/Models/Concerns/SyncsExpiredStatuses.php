<?php

namespace App\Models\Concerns;

/**
 * Tandai otomatis reservasi "mendatang" yang jam selesainya sudah lewat menjadi "selesai".
 *
 * Dipakai bersama oleh RoomReservation dan ZoomReservation (sebelumnya method ini
 * disalin persis di kedua model). Dipanggil oleh middleware SyncReservationStatuses
 * karena aplikasi ini tidak punya scheduler/cron terpisah.
 */
trait SyncsExpiredStatuses
{
    public static function syncExpiredStatuses(): void
    {
        $now = now();

        static::where('status', 'mendatang')
            ->where(function ($query) use ($now) {
                $query->whereDate('tanggal', '<', $now->toDateString())
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('tanggal', $now->toDateString())
                          ->where('jam_selesai', '<=', $now->format('H:i:s'));
                    });
            })
            ->update(['status' => 'selesai']);
    }
}
