<x-layouts.app title="Sukses Reservasi Ruang Rapat">
<div class="max-w-xl mx-auto py-12 px-4">
    <div class="bg-white border rounded-2xl shadow-sm p-8 text-center">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">✓</div>
        <h2 class="text-2xl font-bold text-gray-800">Ruangan berhasil dipesan</h2>
        <p class="text-sm text-gray-500 mt-1">Detail pemesanan reservasi Anda tercatat di sistem.</p>

        <div class="mt-6 text-left border rounded-xl p-4 bg-gray-50 space-y-2 text-sm text-gray-700">
            <p><span class="font-semibold w-32 inline-block">Ruangan:</span> {{ $reservation->room->name }} (Lantai {{ $reservation->room->floor }})</p>
            <p><span class="font-semibold w-32 inline-block">Hari, Tanggal:</span> {{ \Carbon\Carbon::parse($reservation->tanggal)->isoFormat('dddd, D MMMM Y') }}</p>
            <p><span class="font-semibold w-32 inline-block">Waktu:</span> {{ $reservation->jam_range }}</p>
            @if ($reservation->room?->is_combined)
                @php $componentRooms = \App\Models\Room::whereIn('id', $reservation->room->combined_room_ids ?? [])->pluck('name'); @endphp
                <p class="rounded-lg bg-brand-50 px-3 py-2 text-brand-700">
                    Ruang gabungan: {{ $componentRooms->implode(' & ') }} otomatis ikut direservasi pada jam yang sama.
                </p>
            @endif
            <p><span class="font-semibold w-32 inline-block">Tujuan Rapat:</span> {{ $reservation->keperluan }}</p>
            <p><span class="font-semibold w-32 inline-block">Nama PIC:</span> {{ $reservation->nama_pic }}</p>
            <p><span class="font-semibold w-32 inline-block">No. Telp PIC:</span> {{ $reservation->no_telp_pic }}</p>
            <p><span class="font-semibold w-32 inline-block">Divisi PIC:</span> {{ $reservation->divisi_pic ?? '-' }}</p>
            <p><span class="font-semibold w-32 inline-block">Peserta:</span> {{ $reservation->jumlah_peserta }} Orang</p>
            <p><span class="font-semibold w-32 inline-block">Konsumsi:</span> {{ $reservation->konsumsi }}</p>
        </div>

        <div class="mt-8">
            <a href="{{ route('rooms.index') }}" class="inline-block w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow">buat pemesanan lagi</a>
        </div>
    </div>
</div>
</x-layouts.app>