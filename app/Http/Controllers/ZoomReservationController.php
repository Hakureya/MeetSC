<?php

namespace App\Http\Controllers;

use App\Models\ZoomReservation;
use App\Support\TimeSlots;
use Illuminate\Http\Request;

class ZoomReservationController extends Controller
{
    public function index()
    {
        return view('reservations.zoom.index');
    }

    public function getAvailability(Request $request)
    {
        $date = $request->get('tanggal');
        $reservations = ZoomReservation::whereDate('tanggal', $date)
            ->whereIn('status', ['mendatang', 'selesai', 'menunggu_pembatalan'])
            ->with('user')
            ->get(['id', 'nama_agenda', 'nama_pic', 'tanggal', 'jam_mulai', 'jam_selesai', 'room_number', 'user_id']);

        return response()->json($reservations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_agenda' => 'required|string|max:255',
            'nama_pic' => 'required|string|max:255',
            'no_telp_pic' => 'required|string|max:30',
            'divisi_pic' => 'required|string|max:255',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'room_number' => 'required|integer|between:1,9',
        ], [
            'divisi_pic.required' => 'Divisi PIC wajib diisi.',
        ]);

        // Validasi rentang slot (minimal 2 slot),
        // memakai definisi slot yang sama dengan sisi klien.
        if ($slotError = TimeSlots::validateRange($validated['jam_mulai'], $validated['jam_selesai'])) {
            return back()->withErrors(['jam_mulai' => $slotError])->withInput();
        }

        // Cek konflik dengan aturan overlap yang benar, sehingga jam yang bersebelahan
        // (mis. 08.00-09.00 dan 09.00-11.00) tetap boleh dipesan oleh dua pengguna berbeda.
        $conflict = ZoomReservation::whereDate('tanggal', $validated['tanggal'])
            ->where('room_number', $validated['room_number'])
            ->whereIn('status', ['mendatang', 'menunggu_pembatalan'])
            ->where('jam_mulai', '<', $validated['jam_selesai'])
            ->where('jam_selesai', '>', $validated['jam_mulai'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['room_number' => 'Room tersebut telah terisi di jam pilihan Anda.'])->withInput();
        }

        $reservation = ZoomReservation::create([
            'user_id' => auth()->id(),
            'nama_agenda' => $validated['nama_agenda'],
            'nama_pic' => $validated['nama_pic'],
            'no_telp_pic' => $validated['no_telp_pic'],
            'divisi_pic' => $validated['divisi_pic'],
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'room_number' => $validated['room_number'],
            'status' => 'mendatang',
        ]);

        return redirect()->route('zoom.success', $reservation->id);
    }

    public function success($id)
    {
        $reservation = ZoomReservation::with('user')->findOrFail($id);
        return view('reservations.zoom.success', compact('reservation'));
    }
}