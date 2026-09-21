<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\ZoomReservation;

class LandingController extends Controller
{
    public function __invoke()
    {
        // Ruang gabungan (mis. "Ruang Adaptif + Kompeten") sengaja tidak ditampilkan di landing page.
        $rooms = Room::individual()->orderBy('floor')->orderBy('id')->get()->map(function (Room $room) {
            $reservations = $room->reservationsForDate();

            return [
                'id' => $room->id,
                'name' => $room->name,
                'floor' => $room->floor,
                'capacity_label' => $room->capacity_label,
                'image' => $room->image,
                // Merah hanya jika jam SEKARANG berada di dalam salah satu jadwal hari ini,
                // bukan sekadar "ada jadwal hari ini" (yang bisa jadi baru mulai nanti / sudah lewat).
                'in_use' => $room->isInUseNow(),
                'schedules' => $reservations->map(fn ($r) => $r->jam_range)->all(),
            ];
        });

        $availableRooms = $rooms->where('in_use', false)->count();

        // "Ruang zoom" bersifat virtual: 9 slot yang dipakai lintas semua zoom reservation hari ini.
        $now = now();
        $zoomRoomsInUse = ZoomReservation::whereDate('tanggal', $now->toDateString())
            ->where('jam_mulai', '<=', $now->format('H:i:s'))
            ->where('jam_selesai', '>', $now->format('H:i:s'))
            ->where('status', 'mendatang')
            ->distinct()
            ->pluck('room_number')
            ->count();

        return view('landing', [
            'rooms' => $rooms,
            'availableRooms' => $availableRooms,
            'totalRooms' => $rooms->count(),
            'availableZoomRooms' => 9 - $zoomRoomsInUse,
            'totalZoomRooms' => 9,
        ]);
    }
}