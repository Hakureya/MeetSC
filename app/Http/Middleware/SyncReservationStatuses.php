<?php

namespace App\Http\Middleware;

use App\Models\RoomReservation;
use App\Models\ZoomReservation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplikasi ini tidak punya scheduler/cron yang berjalan di background, jadi status
 * reservasi "mendatang" tidak akan pernah otomatis berubah menjadi "selesai" hanya
 * karena jamnya sudah lewat — kecuali ada yang membuka halaman.
 *
 * Middleware ini menjalankan pengecekan tersebut di setiap request halaman web:
 * begitu ada yang membuka web, seluruh reservasi "mendatang" yang jam selesainya
 * sudah lewat langsung ditandai "selesai" sebelum halaman dirender.
 */
class SyncReservationStatuses
{
    public function handle(Request $request, Closure $next): Response
    {
        RoomReservation::syncExpiredStatuses();
        ZoomReservation::syncExpiredStatuses();

        return $next($request);
    }
}