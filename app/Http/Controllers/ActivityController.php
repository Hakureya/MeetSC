<?php

namespace App\Http\Controllers;

use App\Models\RoomReservation;
use App\Models\ZoomReservation;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Riwayat Aktivitas: gabungan reservasi ruang & zoom.
     * Admin melihat aktivitas seluruh pengguna, user biasa hanya melihat aktivitasnya sendiri.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        $search = $request->get('search');
        $jenis = $request->get('jenis'); // 'room' | 'zoom' | null (semua)
        $status = $request->get('status');
        $tanggal = $request->get('tanggal');
        $filterUserId = $request->get('user_id');

        // Reservasi turunan ruang gabungan dikecualikan agar riwayat tetap dihitung satu.
        $roomQuery = RoomReservation::with(['user', 'room'])->where('auto_generated', false);
        $zoomQuery = ZoomReservation::with('user');

        if (! $isAdmin) {
            $roomQuery->where('user_id', $user->id);
            $zoomQuery->where('user_id', $user->id);
        } elseif ($filterUserId) {
            $roomQuery->where('user_id', $filterUserId);
            $zoomQuery->where('user_id', $filterUserId);
        }

        if ($tanggal) {
            $roomQuery->whereDate('tanggal', $tanggal);
            $zoomQuery->whereDate('tanggal', $tanggal);
        }

        if ($status) {
            $roomQuery->where('status', $status);
            $zoomQuery->where('status', $status);
        }

        $rooms = ($jenis === 'zoom') ? collect() : $roomQuery->get()->map(fn ($item) => [
            'id' => null,
            'raw_id' => $item->id,
            'pengguna' => $item->user->name,
            'user' => $item->user,
            'jenis' => 'room',
            'jenis_label' => 'Reservasi Ruang',
            'ruangan' => $item->room->name,
            'keperluan' => $item->keperluan,
            'tanggal' => $item->tanggal->format('d M Y'),
            'tanggal_lengkap' => $item->tanggal->translatedFormat('d F Y'),
            'waktu' => $item->jam_range,
            'status' => $item->status,
            'created_at' => $item->created_at,
            'raw_item' => $item,
        ]);

        $zooms = ($jenis === 'room') ? collect() : $zoomQuery->get()->map(fn ($item) => [
            'id' => null,
            'raw_id' => $item->id,
            'pengguna' => $item->user->name,
            'user' => $item->user,
            'jenis' => 'zoom',
            'jenis_label' => 'Reservasi Zoom',
            'ruangan' => 'Ruang '.$item->room_number,
            'keperluan' => $item->nama_agenda,
            'tanggal' => $item->tanggal->format('d M Y'),
            'tanggal_lengkap' => $item->tanggal->translatedFormat('d F Y'),
            'waktu' => $item->jam_range,
            'status' => $item->status,
            'created_at' => $item->created_at,
            'raw_item' => $item,
        ]);

        // Satu seri nomor berurutan (AKT001, AKT002, ...) diurutkan dari yang paling lama dibuat,
        // supaya ID-nya stabil dan tidak berubah-ubah setiap kali data baru masuk.
        $activities = $rooms->concat($zooms)->sortBy('created_at')->values();
        $activities = $activities->map(function ($a, $i) {
            $a['id'] = 'AKT'.str_pad($i + 1, 3, '0', STR_PAD_LEFT);

            return $a;
        })->sortByDesc('created_at')->values();

        if ($search) {
            $activities = $activities->filter(function ($a) use ($search) {
                return str_contains(strtolower($a['id']), strtolower($search))
                    || str_contains(strtolower($a['pengguna']), strtolower($search));
            })->values();
        }

        return view('activity.index', [
            'activities' => $activities,
            'isAdmin' => $isAdmin,
        ]);
    }
}