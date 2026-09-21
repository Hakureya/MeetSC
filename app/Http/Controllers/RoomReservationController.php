<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomReservation;
use App\Support\KonsumsiOptions;
use App\Support\TimeSlots;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class RoomReservationController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->get('tanggal', Carbon::today()->format('Y-m-d'));

        // Ruang gabungan ikut ditampilkan bersama ruang individu.
        $rooms = Room::orderBy('floor')->orderBy('is_combined')->orderBy('id')->get();

        // Ambil seluruh reservasi pada tanggal tersebut sekali saja, lalu dibagikan
        // ke tiap ruangan. Reservasi turunan (auto_generated) dikecualikan supaya satu
        // pemesanan ruang gabungan tidak terhitung dua kali saat ditampilkan.
        $reservations = RoomReservation::whereDate('tanggal', $selectedDate)
            ->whereIn('status', ['mendatang', 'selesai', 'menunggu_pembatalan'])
            ->where('auto_generated', false)
            ->with(['user', 'room'])
            ->orderBy('jam_mulai')
            ->get();

        $combinedRooms = $rooms->where('is_combined', true);

        // "Sedang dipakai" (merah) hanya berlaku kalau tanggal yang dilihat adalah HARI INI
        // dan jam sekarang berada di dalam salah satu jadwal. Untuk tanggal lain (kemarin/besok),
        // konsep "jam sekarang" tidak relevan, jadi tidak pernah ditandai merah.
        $isToday = $selectedDate === now()->toDateString();
        $now = now()->format('H:i:s');

        foreach ($rooms as $room) {
            // Ketersediaan bersifat dua arah: memesan Ruang Adaptif membuat
            // Ruang Adaptif + Kompeten ikut terpakai, dan sebaliknya.
            if ($room->is_combined) {
                $relatedIds = array_merge([$room->id], $room->combined_room_ids ?? []);
            } else {
                $parentIds = $combinedRooms
                    ->filter(fn (Room $c) => in_array($room->id, $c->combined_room_ids ?? [], true))
                    ->pluck('id')
                    ->all();

                $relatedIds = array_merge([$room->id], $parentIds);
            }

            $roomReservations = $reservations->whereIn('room_id', $relatedIds)->values();

            $room->setRelation('reservations', $roomReservations);

            $room->busy_now = $isToday && $roomReservations->contains(
                fn ($r) => $r->status !== 'dibatalkan'
                    && substr($r->jam_mulai, 0, 8) <= $now
                    && substr($r->jam_selesai, 0, 8) > $now
            );
        }

        return view('reservations.room.index', compact('rooms', 'selectedDate'));
    }

    /**
     * Halaman form pemesanan untuk satu ruangan spesifik (dituju setelah klik "Pesan Ruangan Ini").
     */
    public function create(Request $request, Room $room)
    {
        $selectedDate = $request->get('tanggal', Carbon::today()->format('Y-m-d'));

        // Jadwal terpakai mencakup ruang yang berhubungan (ruang gabungan <-> ruang komponen).
        $relatedIds = $room->relatedRoomIdsForConflictCheck();

        $reservations = RoomReservation::whereIn('room_id', $relatedIds)
            ->whereDate('tanggal', $selectedDate)
            ->whereIn('status', ['mendatang', 'selesai', 'menunggu_pembatalan'])
            ->where('auto_generated', false)
            ->with(['user', 'room'])
            ->orderBy('jam_mulai')
            ->get();

        $room->setRelation('reservations', $reservations);

        // Sama seperti daftar ruangan: merah/"Sedang Dipakai" hanya berlaku untuk
        // tanggal HARI INI dan jam sekarang berada di dalam salah satu jadwal.
        $isToday = $selectedDate === now()->toDateString();
        $now = now()->format('H:i:s');

        $room->busy_now = $isToday && $reservations->contains(
            fn ($r) => $r->status !== 'dibatalkan'
                && substr($r->jam_mulai, 0, 8) <= $now
                && substr($r->jam_selesai, 0, 8) > $now
        );

        return view('reservations.room.create', compact('room', 'selectedDate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'keperluan' => 'required|string|max:255',
            'nama_pic' => 'required|string|max:255',
            'no_telp_pic' => 'required|string|max:30',
            'divisi_pic' => 'required|string|max:255',
            'jumlah_peserta' => 'required|integer|min:1',
            'konsumsi' => ['required', 'string', Rule::in(KonsumsiOptions::acceptedValues())],
            // Wajib diisi hanya bila pemesan memilih opsi terakhir ("Other").
            'konsumsi_lainnya' => 'required_if:konsumsi,'.KonsumsiOptions::OTHER.'|nullable|string|max:255',
        ], [
            'divisi_pic.required' => 'Divisi PIC wajib diisi.',
            'konsumsi_lainnya.required_if' => 'Isi konsumsi lainnya terlebih dahulu.',
        ]);

        // Bila memilih "Other", nilai yang disimpan adalah teks yang diketik pemesan.
        $konsumsi = KonsumsiOptions::resolve(
            $validated['konsumsi'],
            $validated['konsumsi_lainnya'] ?? null
        );

        // Validasi rentang slot (minimal 2 slot),
        // memakai definisi slot yang sama dengan sisi klien.
        if ($slotError = TimeSlots::validateRange($validated['jam_mulai'], $validated['jam_selesai'])) {
            return back()->withErrors(['jam_mulai' => $slotError])->withInput();
        }

        $room = Room::findOrFail($validated['room_id']);
        $roomIdsToCheck = $room->relatedRoomIdsForConflictCheck();

        // Cek konflik reservasi (termasuk ruang gabungan yang berhubungan) dengan aturan
        // overlap yang benar, sehingga jam yang bersebelahan (mis. 08.00-09.00 dan
        // 09.00-11.00) tetap boleh dipesan oleh dua pengguna yang berbeda.
        $conflict = RoomReservation::whereIn('room_id', $roomIdsToCheck)
            ->whereDate('tanggal', $validated['tanggal'])
            ->whereIn('status', ['mendatang', 'menunggu_pembatalan'])
            ->where('jam_mulai', '<', $validated['jam_selesai'])
            ->where('jam_selesai', '>', $validated['jam_mulai'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['jam_mulai' => 'Ruangan sudah dipesan pada rentang jam tersebut.'])->withInput();
        }

        $reservation = RoomReservation::create([
            'room_id' => $room->id,
            'user_id' => auth()->id(),
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'keperluan' => $validated['keperluan'],
            'nama_pic' => $validated['nama_pic'],
            'no_telp_pic' => $validated['no_telp_pic'],
            'divisi_pic' => $validated['divisi_pic'],
            'jumlah_peserta' => $validated['jumlah_peserta'],
            'konsumsi' => $konsumsi,
            'status' => 'mendatang',
        ]);

        // Ruang gabungan: otomatis ikut mereservasi seluruh ruang komponennya di jam yang sama.
        if ($room->is_combined) {
            foreach ($room->combined_room_ids ?? [] as $componentRoomId) {
                RoomReservation::create([
                    'room_id' => $componentRoomId,
                    'parent_reservation_id' => $reservation->id,
                    'auto_generated' => true,
                    'user_id' => auth()->id(),
                    'tanggal' => $validated['tanggal'],
                    'jam_mulai' => $validated['jam_mulai'],
                    'jam_selesai' => $validated['jam_selesai'],
                    'keperluan' => $validated['keperluan'].' (bagian dari ruang gabungan: '.$room->name.')',
                    'nama_pic' => $validated['nama_pic'],
                    'no_telp_pic' => $validated['no_telp_pic'],
                    'divisi_pic' => $validated['divisi_pic'],
                    'jumlah_peserta' => $validated['jumlah_peserta'],
                    'konsumsi' => $konsumsi,
                    'status' => 'mendatang',
                ]);
            }
        }

        return redirect()->route('rooms.success', $reservation->id);
    }

    public function success($id)
    {
        $reservation = RoomReservation::with(['room', 'user'])->findOrFail($id);
        return view('reservations.room.success', compact('reservation'));
    }
}