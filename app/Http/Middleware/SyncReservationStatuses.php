<?php

namespace App\Http\Middleware;

use App\Models\RoomReservation;
use App\Models\ZoomReservation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplikasi ini tidak punya scheduler/cron yang berjalan di background, jadi status
 * reservasi "mendatang" tidak akan pernah otomatis berubah menjadi "selesai" hanya
 * karena jamnya sudah lewat — kecuali ada yang membuka halaman.
 *
 * Middleware ini menjalankan pengecekan tersebut dari request halaman web. Agar tidak
 * menjalankan 2 query UPDATE (yang memindai seluruh tabel) di SETIAP request, pengecekan
 * dibatasi paling sering sekali per INTERVAL detik: request yang datang di antaranya
 * cukup membaca satu kunci cache.
 */
class SyncReservationStatuses
{
    /** Jeda minimum (detik) antar dua kali sinkronisasi status. */
    private const INTERVAL = 60;

    public function handle(Request $request, Closure $next): Response
    {
        // Cache::add() hanya mengembalikan true bila kuncinya belum ada (atau sudah kedaluwarsa),
        // jadi hanya satu request per INTERVAL yang benar-benar menjalankan sinkronisasi.
        if (Cache::add('reservation-status-sync', true, self::INTERVAL)) {
            RoomReservation::syncExpiredStatuses();
            ZoomReservation::syncExpiredStatuses();
        }

        return $next($request);
    }
}
