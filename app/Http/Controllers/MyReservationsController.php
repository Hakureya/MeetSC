<?php

namespace App\Http\Controllers;

use App\Models\RoomReservation;
use App\Models\ZoomReservation;
use Illuminate\Http\Request;

class MyReservationsController extends Controller
{
    /**
     * Menampilkan seluruh reservasi (ruang & zoom) milik user yang sedang login.
     * Ini yang dituju tombol "Lihat Semua Reservasi" di dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $rooms = RoomReservation::with('room')
            ->where('user_id', $user->id)
            // Reservasi turunan ruang gabungan tidak ditampilkan agar riwayat tetap dihitung satu.
            ->where('auto_generated', false)
            ->get()
            ->map(fn ($item) => [
                'id' => 'BK'.str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'raw_id' => $item->id,
                'jenis' => 'room',
                'jenis_label' => 'Ruang Rapat',
                'ruangan' => $item->room->name,
                'keperluan' => $item->keperluan,
                'tanggal' => $item->tanggal->format('d M Y'),
                'waktu' => $item->jam_range,
                'status' => $item->status,
                'created_at' => $item->created_at,
            ]);

        $zooms = ZoomReservation::where('user_id', $user->id)
            ->get()
            ->map(fn ($item) => [
                'id' => 'ZM'.str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'raw_id' => $item->id,
                'jenis' => 'zoom',
                'jenis_label' => 'Meet / Zoom',
                'ruangan' => 'Ruang '.$item->room_number,
                'keperluan' => $item->nama_agenda,
                'tanggal' => $item->tanggal->format('d M Y'),
                'waktu' => $item->jam_range,
                'status' => $item->status,
                'created_at' => $item->created_at,
            ]);

        $reservations = $rooms->concat($zooms)->sortByDesc('created_at')->values();

        return view('reservations.mine', compact('reservations'));
    }
}