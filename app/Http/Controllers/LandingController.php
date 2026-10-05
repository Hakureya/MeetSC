<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomReservation;
use App\Models\ZoomReservation;

class LandingController extends Controller
{
    public function __invoke()
    {
        // Ruang gabungan (mis. "Ruang Adaptif + Kompeten") sengaja tidak ditampilkan di landing page.
        $now = now();
        $nowTime = $now->format('H:i:s');

        // Satu query untuk reservasi aktif HARI INI di semua ruang, lalu dikelompokkan per ruang
        // (sebelumnya: 2 query per ruang -- reservationsForDate() dipanggil lagi oleh isInUseNow()).
        $todayByRoom = RoomReservation::whereDate('tanggal', $now->toDateString())
            ->whereIn('status', ['mendatang', 'menunggu_pembatalan'])
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('room_id');

        $rooms = Room::individual()->orderBy('floor')->orderBy('id')->get()->map(function (Room $room) use ($todayByRoom, $nowTime) {
            $reservations = $todayByRoom->get($room->id, collect());

            return [
                'id' => $room->id,
                'name' => $room->name,
                'floor' => $room->floor,
                'capacity_label' => $room->capacity_label,
                'image' => $room->image,
                // Merah hanya jika jam SEKARANG berada di dalam salah satu jadwal hari ini,
                // bukan sekadar "ada jadwal hari ini" (yang bisa jadi baru mulai nanti / sudah lewat).
                'in_use' => $reservations->contains(fn ($r) => $r->jam_mulai <= $nowTime && $r->jam_selesai > $nowTime),
                'schedules' => $reservations->map(fn ($r) => $r->jam_range)->all(),
            ];
        });

        $availableRooms = $rooms->where('in_use', false)->count();

        // "Ruang zoom" bersifat virtual: 9 slot yang dipakai lintas semua zoom reservation hari ini.
        $zoomRoomsInUse = ZoomReservation::whereDate('tanggal', $now->toDateString())
            ->where('jam_mulai', '<=', $now->format('H:i:s'))
            ->where('jam_selesai', '>', $now->format('H:i:s'))
            ->where('status', 'mendatang')
            ->distinct()
            ->count('room_number');

        return view('landing', [
            'rooms' => $rooms,
            'availableRooms' => $availableRooms,
            'totalRooms' => $rooms->count(),
            'availableZoomRooms' => 9 - $zoomRoomsInUse,
            'totalZoomRooms' => 9,
        ]);
    }
}