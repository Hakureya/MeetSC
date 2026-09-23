<x-layouts.app title="Form Pemesanan Ruangan">
<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('rooms.index', ['tanggal' => $selectedDate]) }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-white border text-slate-500 hover:bg-slate-50">&larr;</a>
        <div>
            <h1 class="text-xl font-bold text-slate-900">Form Pemesanan Ruangan</h1>
            <p class="text-sm text-slate-500">Isi formulir berikut dengan teliti untuk melakukan reservasi ruang rapat.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-[1fr_300px] gap-6 items-start">
        
        <!-- Form Pemesanan -->
        <div class="rounded-2xl border bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('rooms.store') }}" method="POST" id="formRoomReservation">
                @csrf
                <input type="hidden" name="room_id" value="{{ $room->id }}">
                <input type="hidden" name="jam_mulai" id="inputJamMulai">
                <input type="hidden" name="jam_selesai" id="inputJamSelesai">

                <!-- NAMA AGENDA -->
                <div class="mb-5">
                    <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Nama Agenda <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="nama_agenda" 
                        value="{{ old('nama_agenda') }}"
                        placeholder="Contoh: Rapat Koordinasi Mingguan" 
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                </div>

                <!-- NAMA PIC & NO TELP -->
                <div class="mb-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Nama PIC <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            name="nama_pic" 
                            value="{{ old('nama_pic') }}" 
                            placeholder="Nama penanggung jawab rapat" 
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">No. Telpon PIC <span class="text-red-500">*</span></label>
                        <input 
                            type="tel" 
                            name="no_telp_pic" 
                            value="{{ old('no_telp_pic') }}" 
                            placeholder="Contoh: 081234567890" 
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                    </div>
                </div>

                <!-- DIVISI & TANGGAL -->
                <div class="mb-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Divisi PIC <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            name="divisi_pic" 
                            value="{{ old('divisi_pic') }}" 
                            placeholder="Contoh: Renbis" 
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                        <p class="mt-1 text-xs text-gray-400">Divisi atau unit kerja penanggung jawab rapat.</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Hari & Tanggal Penggunaan <span class="text-red-500">*</span></label>
                        <input 
                            type="date" 
                            name="tanggal" 
                            id="reservationDate" 
                            value="{{ $selectedDate }}" 
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                    </div>
                </div>

                <!-- JAM PENGGUNAAN -->
                <div class="mb-5">
                    <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Jam Penggunaan (15 Slot Waktu) <span class="text-red-500">*</span></label>
                    <p class="mb-2 text-xs text-gray-400">Klik satu slot untuk memesan 30 menit, atau klik slot lain setelahnya untuk memesan rentang jam. Klik ulang slot yang sama untuk membatalkan pilihan.</p>
                    <div id="slotDotsContainer" class="grid grid-cols-3 gap-2 rounded-xl border border-dashed border-slate-300 p-3 bg-gray-50"></div>
                    <div class="mt-2 flex flex-wrap items-center gap-4 text-[11px] text-gray-500">
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-gray-300"></span> Tersedia</span>
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Dipilih</span>
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-red-500"></span> Sudah Dipesan</span>
                    </div>
                </div>

                <!-- TUJUAN PENGGUNAAN -->
                <div class="mb-5">
                    <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Tujuan Penggunaan Rapat <span class="text-red-500">*</span></label>
                    <textarea 
                        name="keperluan" 
                        rows="3" 
                        required 
                        placeholder="Masukkan agenda atau tujuan rapat..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >{{ old('keperluan') }}</textarea>
                </div>

                <!-- JUMLAH ORANG -->
                <div class="mb-5">
                    <label class="mb-1 block text-xs font-semibold uppercase text-gray-700">Jumlah Orang yang Hadir <span class="text-red-500">*</span></label>
                    <input 
                        type="number" 
                        name="jumlah_peserta" 
                        value="{{ old('jumlah_peserta') }}"
                        min="1" 
                        required 
                        placeholder="Contoh: 10"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                </div>

                <!-- KONSUMSI -->
                <div class="mb-6">
                    <label class="mb-2 block text-xs font-semibold uppercase text-gray-700">Konsumsi <span class="text-red-500">*</span></label>
                    <div class="space-y-2 text-sm">
                        <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="konsumsi" value="Tanpa Konsumsi" required @if(old('konsumsi') === 'Tanpa Konsumsi') checked @endif onchange="toggleKonsumsiLainnya()" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700">Tanpa Konsumsi</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="konsumsi" value="Snack" required @if(old('konsumsi') === 'Snack') checked @endif onchange="toggleKonsumsiLainnya()" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700">Snack</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="konsumsi" value="Makan Siang" required @if(old('konsumsi') === 'Makan Siang') checked @endif onchange="toggleKonsumsiLainnya()" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700">Makan Siang</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="konsumsi" value="Makan Malam" required @if(old('konsumsi') === 'Makan Malam') checked @endif onchange="toggleKonsumsiLainnya()" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700">Makan Malam</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="konsumsi" value="Snack dan Makan Siang" required @if(old('konsumsi') === 'Snack dan Makan Siang') checked @endif onchange="toggleKonsumsiLainnya()" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700">Snack dan Makan Siang</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="konsumsi" value="Disiapkan PLN NP/PLN IP/Eksternal" required @if(old('konsumsi') === 'Disiapkan PLN NP/PLN IP/Eksternal') checked @endif onchange="toggleKonsumsiLainnya()" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700">Disiapkan PLN NP/PLN IP/Eksternal</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="konsumsi" id="konsumsiOther" value="Other" required @if(old('konsumsi') === 'Other') checked @endif onchange="toggleKonsumsiLainnya()" class="text-blue-600 focus:ring-blue-500">
                            <span class="text-gray-700">Other:</span>
                        </label>
                        <input 
                            type="text" 
                            name="konsumsi_lainnya" 
                            id="konsumsiLainnya"
                            value="{{ old('konsumsi_lainnya') }}"
                            placeholder="Tulis kebutuhan konsumsi lainnya"
                            maxlength="255" 
                            disabled
                            class="ml-6 w-[calc(100%-1.5rem)] rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:border-slate-200"
                        >
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
                $hasSchedule =$room->reservations->isNotEmpty();
                $isBusyNow =$room->busy_now;
            @endphp
            <div class="overflow-hidden rounded-xl border {{ $isBusyNow ? 'border-red-200' : ($hasSchedule ? 'border-amber-200' : 'border-emerald-200') }}">
                <div class="relative h-36 w-full bg-slate-200">
                    @if ($room->image)
                        <img src="{{ asset('storage/'.$room->image) }}" alt="{{ $room->name }}" class="h-full w-full object-cover">
                    @endif
                    <span class="absolute right-2 top-2 rounded-full px-2.5 py-1 text-[11px] font-bold text-white {{ $isBusyNow ? 'bg-red-600' : ($hasSchedule ? 'bg-amber-500' : 'bg-emerald-500') }}">
                        {{ $isBusyNow ? 'Sedang Dipakai' : ($hasSchedule ? 'Ada Jadwal' : 'Tersedia') }}
                    </span>
                </div>
                <div class="p-3 {{ $isBusyNow ? 'bg-red-50' : ($hasSchedule ? 'bg-amber-50' : 'bg-emerald-50') }}">
                    <p class="font-semibold text-slate-800">{{ $room->name }}</p>

                    @if ($room->is_combined)
                        @php $componentRooms = \App\Models\Room::whereIn('id',$room->combined_room_ids ?? [])->pluck('name'); @endphp
                        <p class="mt-1.5 rounded-md bg-blue-50 px-2 py-1 text-[10px] font-semibold leading-relaxed text-blue-600">
                            Ruang Gabungan — memesan ini otomatis ikut memesan {{ $componentRooms->implode(' & ') }} pada jam yang sama.
                        </p>
                    @endif

                    @if ($hasSchedule)
                        <p class="mt-1 text-xs font-medium {{ $isBusyNow ? 'text-red-700' : 'text-amber-700' }}">
                            {{ $isBusyNow ? 'Dipakai sekarang:' : 'Jadwal hari ini:' }}
                        </p>
                        <ul class="mt-1 max-h-10 space-y-0.5 overflow-y-auto pr-1">
                            @if(count($room->reservations) > 0)
                                @for($i = 0; $i < count($room->reservations);$i++)
                                    <li class="flex items-center gap-1.5 text-xs {{ $isBusyNow ? 'text-red-700' : 'text-amber-700' }}">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $isBusyNow ? 'bg-red-700' : 'bg-amber-500' }}"></span>
                                        {{ substr($room->reservations[$i]->jam_mulai, 0, 5) }} – {{ substr($room->reservations[$i]->jam_selesai, 0, 5) }}
                                        @if ($room->reservations[$i]->room_id !==$room->id)
                                            <span class="text-[10px] opacity-70">({{ $room->reservations[$i]->room->name }})</span>
                                        @endif
                                    </li>
                                @endfor
                            @endif
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

<!-- Modal Custom Alert (pengganti alert() bawaan browser) -->
<div id="customAlertModal" class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div id="customAlertBox" class="w-full max-w-xs rounded-xl bg-red-600 p-4 shadow-2xl">
        <div class="flex items-start gap-2.5">
            <div id="customAlertIconWrap" class="flex h-8 w-8 shrink-0 items-center justify-center text-white">
                <svg id="customAlertIcon" class="h-5 w-5" viewBox="0 0 20 20" fill="none">
                    <path d="M10 6v4.5M10 14h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="10" cy="10" r="8.25" stroke="currentColor" stroke-width="1.8"/>
                </svg>
            </div>
            <div class="flex-1 pt-0.5">
                <h3 id="customAlertTitle" class="text-sm font-bold text-white">Pemberitahuan</h3>
                <p id="customAlertMessage" class="mt-1 text-xs leading-relaxed text-white"></p>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button type="button" id="customAlertOkBtn" class="rounded-full bg-white px-4 py-1.5 text-xs font-bold text-blue-600 shadow transition hover:bg-blue-50">
                OK
            </button>
        </div>
    </div>
</div>

<script>
const timeSlots = @json(\App\Support\TimeSlots::all());
const totalSlots = timeSlots.length;

let selectedRange = [];
let pendingStart = null;

@php
    $reservationsForJs =$room->reservations->map(function ($r) use ($room) {
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

// ===== Modal Alert Custom (pengganti alert() bawaan) =====
const customAlertModal = document.getElementById('customAlertModal');
const customAlertBox = document.getElementById('customAlertBox');
const customAlertIconWrap = document.getElementById('customAlertIconWrap');
const customAlertIcon = document.getElementById('customAlertIcon');
const customAlertTitle = document.getElementById('customAlertTitle');
const customAlertMessage = document.getElementById('customAlertMessage');
const customAlertOkBtn = document.getElementById('customAlertOkBtn');

function showCustomAlert(message, { title = 'Pemberitahuan', type = 'danger' } = {}) {
    const styles = {
        danger: { box: 'bg-red-600' },
        warning: { box: 'bg-amber-500' },
        info: { box: 'bg-blue-600' },
    };
    const style = styles[type] || styles.danger;

    customAlertBox.className = `w-full max-w-xs rounded-xl p-4 shadow-2xl ${style.box}`;
    customAlertIconWrap.className = 'flex h-10 w-10 shrink-0 items-center justify-center text-white';
    customAlertTitle.className = 'text-sm font-bold text-white';
    customAlertTitle.textContent = title;
    customAlertMessage.className = 'mt-1 text-xs leading-relaxed text-white';
    customAlertMessage.innerHTML = message;
    customAlertOkBtn.className = 'rounded-full bg-white px-4 py-1.5 text-xs font-bold text-blue-600 shadow transition hover:bg-blue-50';

    customAlertModal.classList.remove('hidden');
    customAlertModal.classList.add('flex');
}

function closeCustomAlert() {
    customAlertModal.classList.add('hidden');
    customAlertModal.classList.remove('flex');
}

customAlertOkBtn.addEventListener('click', closeCustomAlert);
customAlertModal.addEventListener('click', (e) => {
    if (e.target === customAlertModal) closeCustomAlert();
});
// ===========================================================

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
            btn.onclick = () => showCustomAlert(
                `Ruangan sudah dipesan oleh: <b>${res.user ? res.user.name : 'Pengguna Lain'}</b><br>`
                + `Keperluan: ${res.keperluan}<br>`
                + `Jam: ${res.jam_mulai.slice(0,5)} - ${res.jam_selesai.slice(0,5)}`
                + (res.ruang_lain ? `<br>Terpakai melalui: ${res.ruang_lain}` : ''),
                { title: 'Slot Sudah Dipesan', type: 'danger' }
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
            showCustomAlert('Tidak dapat memilih rentang yang melompati jam terpesan!', { title: 'Rentang Tidak Valid', type: 'warning' });
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