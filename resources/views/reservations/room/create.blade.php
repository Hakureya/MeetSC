<x-layouts.app title="Form Pemesanan Ruangan">
<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('rooms.index', ['tanggal' => $selectedDate]) }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-white border text-slate-500 hover:bg-slate-50">&larr;</a>
        <div>
            <h1 class="text-xl font-bold text-slate-900">Form Pemesanan Ruangan</h1>
            <p class="text-sm text-slate-500">Isi formulir berikut dengan teliti untuk melakukan reservasi ruang rapat.</p>
        </div>
    </div>

    <!-- Layout Grid 2 Kolom (Form Kiri, Detail Ruangan Kanan) -->
    <div class="grid grid-cols-1 md:grid-cols-[1fr_300px] gap-6 items-start">
        
        <!-- Form Pemesanan -->
        <div class="rounded-2xl border bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-busy-border bg-busy-bg px-4 py-3 text-sm text-busy-text">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('rooms.store') }}" method="POST" id="formRoomReservation">
                @csrf
                <input type="hidden" name="room_id" value="{{ $room->id }}">
                <input type="hidden" name="jam_mulai" id="inputJamMulai">
                <input type="hidden" name="jam_selesai" id="inputJamSelesai">

                <div class="mb-5">
                    <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Nama Agenda</label>
                    <input type="text" name="nama_agenda" placeholder="Contoh: Rapat Koordinasi Mingguan" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="mb-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Nama PIC</label>
                        <input type="text" name="nama_pic" value="{{ old('nama_pic') }}" placeholder="Nama penanggung jawab rapat" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">No. Telpon PIC</label>
                        <input type="tel" name="no_telp_pic" value="{{ old('no_telp_pic') }}" placeholder="Contoh: 081234567890" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Divisi PIC</label>
                        <input type="text" name="divisi_pic" value="{{ old('divisi_pic') }}" placeholder="Contoh: Renbis" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-400">Divisi atau unit kerja penanggung jawab rapat.</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Hari & Tanggal Penggunaan</label>
                        <input type="date" name="tanggal" id="reservationDate" value="{{ $selectedDate }}" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Jam Penggunaan (15 Slot Waktu)</label>
                    <p class="mb-2 text-xs text-gray-400">Klik satu slot untuk memesan 30 menit, atau klik slot lain setelahnya untuk memesan rentang jam. Klik ulang slot yang sama untuk membatalkan pilihan.</p>
                    <div id="slotDotsContainer" class="grid grid-cols-3 gap-2 rounded-xl border border-dashed p-3"></div>
                    <div class="mt-2 flex flex-wrap items-center gap-4 text-[11px] text-gray-500">
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-gray-300"></span> Tersedia</span>
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Dipilih</span>
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-red-500"></span> Sudah Dipesan</span>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Tujuan Penggunaan Rapat</label>
                    <textarea name="keperluan" rows="3" required placeholder="Masukkan agenda atau tujuan rapat..."
                              class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div class="mb-5">
                    <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Jumlah Orang yang Hadir</label>
                    <input type="number" name="jumlah_peserta" min="1" required placeholder="Contoh: 10"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="mb-6">
                    <label class="mb-2 block text-xs font-semibold uppercase text-gray-700">Konsumsi</label>
                    <div class="space-y-2 text-sm">
                        @foreach (\App\Support\KonsumsiOptions::preset() as $item)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="konsumsi" value="{{ $item }}" required
                                       @checked(old('konsumsi') === $item)
                                       onchange="toggleKonsumsiLainnya()"
                                       class="text-blue-600 focus:ring-blue-500">
                                <span class="text-gray-700">{{ $item }}</span>
                            </label>
                        @endforeach

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="konsumsi" id="konsumsiOther" value="{{ \App\Support\KonsumsiOptions::OTHER }}" required
                                   @checked(old('konsumsi') === \App\Support\KonsumsiOptions::OTHER)
                                   onchange="toggleKonsumsiLainnya()"
                                   class="text-blue-600 focus:ring-blue-500">
                            <span class="text-gray-700">Other:</span>
                        </label>
                        <input type="text" name="konsumsi_lainnya" id="konsumsiLainnya"
                               value="{{ old('konsumsi_lainnya') }}"
                               placeholder="Tulis kebutuhan konsumsi lainnya"
                               maxlength="255" disabled
                               class="ml-6 w-[calc(100%-1.5rem)] rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400">
                    </div>
                </div>

                <button type="submit" id="btnSubmitRoom" disabled
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-gray-300 py-3 font-bold text-gray-500 transition cursor-not-allowed">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="m4 10 4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Pilih Jam Terlebih Dahulu
                </button>
            </form>
        </div>

        <!-- Detail Ruangan (Samping Kanan) -->
        <div class="h-fit rounded-2xl border bg-white p-4 shadow-sm md:sticky md:top-6">
            <h3 class="mb-3 text-sm font-bold text-slate-800">Detail Ruangan</h3>
            @php
                $hasSchedule = $room->reservations->isNotEmpty();
                $isBusyNow = $room->busy_now;
            @endphp
            <div class="overflow-hidden rounded-xl border {{ $isBusyNow ? 'border-busy-border' : ($hasSchedule ? 'border-amber-200' : 'border-free-border') }}">
                <div class="relative h-36 w-full bg-slate-200">
                    @if ($room->image)
                        <img src="{{ asset('storage/'.$room->image) }}" alt="{{ $room->name }}" class="h-full w-full object-cover">
                    @endif
                    <span class="absolute right-2 top-2 rounded-full px-2.5 py-1 text-[11px] font-bold text-white {{ $isBusyNow ? 'bg-red-600' : ($hasSchedule ? 'bg-amber-500' : 'bg-emerald-500') }}">
                        {{ $isBusyNow ? 'Sedang Dipakai' : ($hasSchedule ? 'Ada Jadwal' : 'Tersedia') }}
                    </span>
                </div>
                <div class="p-3 {{ $isBusyNow ? 'bg-busy-bg' : ($hasSchedule ? 'bg-amber-50' : 'bg-free-bg') }}">
                    <p class="font-semibold text-slate-800">{{ $room->name }}</p>

                    @if ($room->is_combined)
                        @php $componentRooms = \App\Models\Room::whereIn('id', $room->combined_room_ids ?? [])->pluck('name'); @endphp
                        <p class="mt-1.5 rounded-md bg-brand-50 px-2 py-1 text-[10px] font-semibold leading-relaxed text-brand-600">
                            Ruang Gabungan — memesan ini otomatis ikut memesan {{ $componentRooms->implode(' & ') }} pada jam yang sama.
                        </p>
                    @endif

                    @if ($hasSchedule)
                        <p class="mt-1 text-xs font-medium {{ $isBusyNow ? 'text-busy-text' : 'text-amber-700' }}">
                            {{ $isBusyNow ? 'Dipakai sekarang:' : 'Jadwal hari ini:' }}
                        </p>
                        <ul class="mt-1 max-h-10 space-y-0.5 overflow-y-auto pr-1">
                            @foreach ($room->reservations as $res)
                                <li class="flex items-center gap-1.5 text-xs {{ $isBusyNow ? 'text-busy-text' : 'text-amber-700' }}">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $isBusyNow ? 'bg-busy-text' : 'bg-amber-500' }}"></span>
                                    {{ substr($res->jam_mulai, 0, 5) }} – {{ substr($res->jam_selesai, 0, 5) }}
                                    @if ($res->room_id !== $room->id)
                                        <span class="text-[10px] opacity-70">({{ $res->room->name }})</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-1 text-xs text-emerald-600 font-medium">• Belum ada reservasi untuk hari ini</p>
                    @endif
                </div>
                <div class="border-t bg-white px-3 py-2 text-xs font-semibold text-slate-600">
                    Kapasitas {{ $room->capacity_label }}
                </div>
            </div>
        </div>

    </div>
</div>

<script>
const timeSlots = @json(\App\Support\TimeSlots::all());
const totalSlots = timeSlots.length;

let selectedRange = [];
let pendingStart = null;

@php
    $reservationsForJs = $room->reservations->map(function ($r) use ($room) {
        return [
            'jam_mulai' => $r->jam_mulai,
            'jam_selesai' => $r->jam_selesai,
            'user' => $r->user ? ['name' => $r->user->name] : null,
            'keperluan' => $r->keperluan,
            'ruang_lain' => $r->room_id !== $room->id ? ($r->room->name ?? null) : null,
        ];
    })->values();
@endphp
const currentReservations = @json($reservationsForJs);

function slotHasReservationConflict(idx) {
    const slot = timeSlots[idx];
    return currentReservations.find(r => slot.start < r.jam_selesai.slice(0,5) && slot.end > r.jam_mulai.slice(0,5));
}

function renderDots() {
    const container = document.getElementById('slotDotsContainer');
    container.innerHTML = '';

    for (let index = 0; index < totalSlots; index++) {
        const slot = timeSlots[index];
        const res = slotHasReservationConflict(index);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'flex flex-col items-center justify-center gap-0.5 p-2 rounded-lg border text-[10px] font-semibold transition relative';

        if (res) {
            btn.classList.add('bg-red-500', 'border-red-600', 'text-white', 'cursor-pointer');
            btn.innerHTML = `<span>${slot.label}</span><span class="w-2 h-2 rounded-full bg-white"></span>`;
            btn.onclick = () => alert(
                `Ruangan sudah dipesan oleh: ${res.user ? res.user.name : 'Pengguna Lain'}\n`
                + `Keperluan: ${res.keperluan}\n`
                + `Jam: ${res.jam_mulai.slice(0,5)} - ${res.jam_selesai.slice(0,5)}`
                + (res.ruang_lain ? `\nTerpakai melalui: ${res.ruang_lain}` : '')
            );
        } else {
            btn.classList.add('bg-gray-100', 'border-gray-200', 'text-gray-600', 'hover:border-emerald-500');
            btn.innerHTML = `<span>${slot.label}</span><span class="w-2 h-2 rounded-full bg-gray-300 dot-circle"></span>`;
            btn.onclick = () => handleSlotClick(index);
        }

        btn.id = `slot-${index}`;
        container.appendChild(btn);
    }
}

function resetSlotSelection() {
    pendingStart = null;
    selectedRange = [];
    renderHighlight();
    updateFormInputs();
}

function handleSlotClick(idx) {
    if (pendingStart === null) {
        pendingStart = idx;
        selectedRange = [idx, idx];
        renderHighlight();
        updateFormInputs();
        return;
    }

    if (idx === pendingStart) {
        resetSlotSelection();
        return;
    }

    const [min, max] = [Math.min(pendingStart, idx), Math.max(pendingStart, idx)];

    for (let i = min; i <= max; i++) {
        if (slotHasReservationConflict(i)) {
            alert('Tidak dapat memilih rentang yang melompati jam terpesan!');
            resetSlotSelection();
            return;
        }
    }

    selectedRange = [min, max];
    pendingStart = null;
    renderHighlight();
    updateFormInputs();
}

function renderHighlight() {
    for (let index = 0; index < totalSlots; index++) {
        const el = document.getElementById(`slot-${index}`);
        if (!el || el.classList.contains('bg-red-500')) continue;

        const inRange = selectedRange.length === 2
            && index >= selectedRange[0]
            && index <= selectedRange[1];

        const circle = el.querySelector('.dot-circle');
        if (inRange) {
            el.classList.add('bg-emerald-500', 'border-emerald-600', 'text-white');
            el.classList.remove('bg-gray-100', 'text-gray-600');
            if (circle) circle.className = 'w-2 h-2 rounded-full bg-white dot-circle';
        } else {
            el.classList.remove('bg-emerald-500', 'border-emerald-600', 'text-white');
            el.classList.add('bg-gray-100', 'text-gray-600');
            if (circle) circle.className = 'w-2 h-2 rounded-full bg-gray-300 dot-circle';
        }
    }
}

function updateFormInputs() {
    const btn = document.getElementById('btnSubmitRoom');

    if (selectedRange.length === 2) {
        const startSlot = timeSlots[selectedRange[0]];
        const endSlot = timeSlots[selectedRange[1]];

        document.getElementById('inputJamMulai').value = startSlot.start;
        document.getElementById('inputJamSelesai').value = endSlot.end;

        btn.disabled = false;
        btn.className = 'flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 font-bold text-white transition hover:bg-blue-700 shadow-lg shadow-blue-500/30';
        btn.innerHTML = `Konfirmasi Pemesanan (${startSlot.start} - ${endSlot.end})`;
    } else {
        document.getElementById('inputJamMulai').value = '';
        document.getElementById('inputJamSelesai').value = '';

        btn.disabled = true;
        btn.className = 'flex w-full items-center justify-center gap-2 rounded-xl bg-gray-300 py-3 font-bold text-gray-500 transition cursor-not-allowed';
        btn.innerText = 'Pilih Jam Terlebih Dahulu';
    }
}

function toggleKonsumsiLainnya(focusInput = true) {
    const isOther = document.getElementById('konsumsiOther').checked;
    const input = document.getElementById('konsumsiLainnya');

    input.disabled = !isOther;
    input.required = isOther;

    if (isOther) {
        if (focusInput) input.focus();
    } else {
        input.value = '';
    }
}

renderDots();
toggleKonsumsiLainnya(false);
</script>
</x-layouts.app>