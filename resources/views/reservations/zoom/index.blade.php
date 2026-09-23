<x-layouts.app title="Reservasi Link Zoom">
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white border rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-gray-100">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Reservasi Breakout Room Zoom</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pilih rentang slot waktu dan breakout room yang tersedia.</p>
            </div>
            <a 
                href="{{ route('zoom.index') }}" 
                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 hover:text-emerald-700 border border-gray-200 rounded-lg transition duration-150 shadow-xs shrink-0"
            >
                <span>Kembali ke Daftar Reservasi</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-busy-border bg-busy-bg px-4 py-3 text-sm text-busy-text">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('zoom.store') }}" method="POST" id="formZoom">
            @csrf
            <input type="hidden" name="jam_mulai" id="inputZoomMulai">
            <input type="hidden" name="jam_selesai" id="inputZoomSelesai">
            <input type="hidden" name="room_number" id="inputZoomRoom">

            <!-- NAMA AGENDA -->
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nama Agenda <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    name="nama_agenda" 
                    value="{{ old('nama_agenda') }}" 
                    required 
                    placeholder="Contoh: Rapat Evaluasi Kerja" 
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <!-- NAMA PIC & NO TELP -->
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nama PIC <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="nama_pic" 
                        value="{{ old('nama_pic') }}" 
                        required 
                        placeholder="Nama penanggung jawab rapat" 
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">No. Telpon PIC <span class="text-red-500">*</span></label>
                    <input 
                        type="tel" 
                        name="no_telp_pic" 
                        value="{{ old('no_telp_pic') }}" 
                        required 
                        placeholder="Contoh: 081234567890" 
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                </div>
            </div>

            <!-- DIVISI PIC -->
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Divisi PIC <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    name="divisi_pic" 
                    value="{{ old('divisi_pic') }}" 
                    required 
                    placeholder="Contoh: Renbis" 
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
                <p class="text-[11px] text-gray-400 mt-1">Divisi atau unit kerja penanggung jawab rapat.</p>
            </div>

            <!-- HARI DAN TANGGAL -->
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Hari dan Tanggal <span class="text-red-500">*</span></label>
                <input 
                    type="date" 
                    name="tanggal" 
                    id="zoomDate" 
                    value="{{ date('Y-m-d') }}" 
                    onchange="fetchZoomAvailability()" 
                    required 
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <!-- PILIH JAM -->
            <div class="mb-5">
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Pilih Jam (15 Slot Waktu) <span class="text-red-500">*</span></label>
                <div id="zoomTimeDots" class="grid grid-cols-3 gap-2 p-3 bg-gray-50 rounded-xl border border-slate-200"></div>
                <p class="text-[11px] text-gray-400 mt-1.5">Klik satu slot untuk memesan 30 menit, atau klik slot lain setelahnya untuk memesan rentang jam. Klik ulang slot yang sama untuk membatalkan pilihan. *Slot merah menunjukkan seluruh ruangan pada jam tersebut penuh.</p>
            </div>

            <!-- PILIH ROOM ZOOM -->
            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Pilih Room Zoom (Ruang 1 – Ruang 9) <span class="text-red-500">*</span></label>
                <div id="roomDotsContainer" class="grid grid-cols-9 gap-2">
                    @for($i=1; $i<=9; $i++)
                        <button type="button" id="room-dot-{{ $i }}" onclick="selectZoomRoom({{ $i }})" class="p-3 border rounded-xl flex flex-col items-center bg-gray-100 text-gray-600 transition">
                            <span class="text-xs font-bold">R{{ $i }}</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-gray-300 mt-1 room-indicator"></span>
                        </button>
                    @endfor
                </div>
            </div>

            <button type="submit" id="btnSubmitZoom" disabled class="w-full py-3 bg-gray-300 text-gray-500 font-bold rounded-xl cursor-not-allowed">Lengkapi Jam dan Ruangan</button>
        </form>
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
const slots = @json(\App\Support\TimeSlots::all());
const totalZoomSlots = slots.length;

let zoomReservations = [];
let selectedTime = [];
let pendingZoomStart = null;
let activeRoom = null;

// ===== Modal Alert Custom (pengganti alert() bawaan) =====
const customAlertModal = document.getElementById('customAlertModal');
const customAlertBox = document.getElementById('customAlertBox');
const customAlertIconWrap = document.getElementById('customAlertIconWrap');
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
    customAlertIconWrap.className = 'flex h-8 w-8 shrink-0 items-center justify-center text-white';
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

async function fetchZoomAvailability() {
    const d = document.getElementById('zoomDate').value;
    try {
        const res = await fetch(`{{ route('zoom.availability') }}?tanggal=${d}`);
        if (!res.ok) throw new Error('Gagal memuat ketersediaan');
        zoomReservations = await res.json();
    } catch (e) {
        console.error(e);
        zoomReservations = [];
    }
    selectedTime = [];
    pendingZoomStart = null;
    activeRoom = null;
    document.getElementById('inputZoomMulai').value = '';
    document.getElementById('inputZoomSelesai').value = '';
    document.getElementById('inputZoomRoom').value = '';
    renderTimeSlots();
    refreshRooms();
    validateZoomForm();
}

function zoomSlotIsFull(idx) {
    const slot = slots[idx];
    const bookedRooms = zoomReservations.filter(r => slot.start < r.jam_selesai.slice(0,5) && slot.end > r.jam_mulai.slice(0,5));
    return bookedRooms.length >= 9;
}

function renderTimeSlots() {
    const container = document.getElementById('zoomTimeDots');
    container.innerHTML = '';

    for (let idx = 0; idx < totalZoomSlots; idx++) {
        const slot = slots[idx];
        const isFull = zoomSlotIsFull(idx);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'flex flex-col items-center gap-0.5 p-2 rounded-lg border text-[10px] font-semibold transition';

        if (isFull) {
            btn.classList.add('bg-red-500', 'border-red-600', 'text-white');
            btn.title = "Waktu ini ruangannya penuh semua";
            btn.onclick = () => showCustomAlert('Waktu ini ruangannya penuh semua.', { title: 'Semua Ruangan Penuh', type: 'danger' });
            btn.innerHTML = `<span>${slot.label}</span><span class="w-2 h-2 rounded-full bg-white"></span>`;
        } else {
            btn.classList.add('bg-gray-100', 'text-gray-600', 'hover:border-emerald-500');
            btn.innerHTML = `<span>${slot.label}</span><span class="w-2 h-2 rounded-full bg-gray-300 time-indicator"></span>`;
            btn.onclick = () => handleTimeSelect(idx);
        }
        btn.id = `zslot-${idx}`;
        container.appendChild(btn);
    }
}

function resetZoomSelection() {
    selectedTime = [];
    pendingZoomStart = null;
    document.getElementById('inputZoomMulai').value = '';
    document.getElementById('inputZoomSelesai').value = '';
    highlightZoomSlots();
}

function handleTimeSelect(idx) {
    if (pendingZoomStart === null) {
        pendingZoomStart = idx;
        selectedTime = [idx, idx];

        document.getElementById('inputZoomMulai').value = slots[idx].start;
        document.getElementById('inputZoomSelesai').value = slots[idx].end;

        highlightZoomSlots();
        refreshRooms();
        return;
    }

    if (idx === pendingZoomStart) {
        resetZoomSelection();
        refreshRooms();
        return;
    }

    const min = Math.min(pendingZoomStart, idx);
    const max = Math.max(pendingZoomStart, idx);

    for (let i = min; i <= max; i++) {
        if (zoomSlotIsFull(i)) {
            showCustomAlert('Tidak dapat memilih rentang yang melompati jam yang penuh semua!', { title: 'Rentang Tidak Valid', type: 'warning' });
            resetZoomSelection();
            refreshRooms();
            return;
        }
    }

    selectedTime = [min, max];
    pendingZoomStart = null;

    document.getElementById('inputZoomMulai').value = slots[min].start;
    document.getElementById('inputZoomSelesai').value = slots[max].end;

    highlightZoomSlots();
    refreshRooms();
}

function highlightZoomSlots() {
    for (let index = 0; index < totalZoomSlots; index++) {
        const el = document.getElementById(`zslot-${index}`);
        if (!el || el.classList.contains('bg-red-500')) continue;

        const isSelected = selectedTime.length === 2
            && index >= selectedTime[0]
            && index <= selectedTime[1];

        const circle = el.querySelector('.time-indicator');

        if (isSelected) {
            el.classList.add('bg-emerald-500', 'border-emerald-600', 'text-white');
            el.classList.remove('bg-gray-100', 'text-gray-600');
            if (circle) circle.className = 'w-2 h-2 rounded-full bg-white time-indicator';
        } else {
            el.classList.remove('bg-emerald-500', 'border-emerald-600', 'text-white');
            el.classList.add('bg-gray-100', 'text-gray-600');
            if (circle) circle.className = 'w-2 h-2 rounded-full bg-gray-300 time-indicator';
        }
    }
}

function refreshRooms() {
    if (selectedTime.length !== 2) {
        for (let r = 1; r <= 9; r++) {
            const btn = document.getElementById(`room-dot-${r}`);
            btn.className = "p-3 border rounded-xl flex flex-col items-center bg-gray-100 text-gray-400 cursor-not-allowed transition";
            btn.onclick = null;
            const ind = btn.querySelector('.room-indicator');
            if (ind) ind.className = 'w-2.5 h-2.5 rounded-full bg-gray-300 mt-1 room-indicator';
        }
        validateZoomForm();
        return;
    }

    const start = slots[selectedTime[0]].start;
    const end = slots[selectedTime[1]].end;

    for (let r = 1; r <= 9; r++) {
        const btn = document.getElementById(`room-dot-${r}`);
        const booked = zoomReservations.find(res => res.room_number == r && (start < res.jam_selesai.slice(0,5) && end > res.jam_mulai.slice(0,5)));

        btn.className = "p-3 border rounded-xl flex flex-col items-center transition";
        const ind = btn.querySelector('.room-indicator');

        if (booked) {
            btn.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
            ind.className = 'w-2.5 h-2.5 rounded-full bg-red-500 mt-1 room-indicator';
            btn.onclick = () => showCustomAlert(
                `Agenda: ${booked.nama_agenda}<br>`
                + `PIC: ${booked.nama_pic || '-'}<br>`
                + `Jam: ${booked.jam_mulai.slice(0,5)} - ${booked.jam_selesai.slice(0,5)}`,
                { title: `Ruang ${r} Sudah Dipesan`, type: 'danger' }
            );
        } else if (activeRoom === r) {
            btn.classList.add('bg-emerald-500', 'border-emerald-600', 'text-white');
            ind.className = 'w-2.5 h-2.5 rounded-full bg-white mt-1 room-indicator';
            btn.onclick = () => selectZoomRoom(r);
        } else {
            btn.classList.add('bg-gray-50', 'text-gray-600', 'hover:border-emerald-500');
            ind.className = 'w-2.5 h-2.5 rounded-full bg-gray-300 mt-1 room-indicator';
            btn.onclick = () => selectZoomRoom(r);
        }
    }
    validateZoomForm();
}

function selectZoomRoom(num) {
    activeRoom = num;
    document.getElementById('inputZoomRoom').value = num;
    refreshRooms();
}

function validateZoomForm() {
    const btn = document.getElementById('btnSubmitZoom');
    if (selectedTime.length === 2 && activeRoom) {
        btn.disabled = false;
        btn.className = "w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow";
        btn.innerText = "Konfirmasi Reservasi Zoom";
    } else {
        btn.disabled = true;
        btn.className = "w-full py-3 bg-gray-300 text-gray-500 font-bold rounded-xl cursor-not-allowed";
        btn.innerText = selectedTime.length === 2 ? "Pilih Room Zoom" : "Lengkapi Jam dan Ruangan";
    }
}

fetchZoomAvailability();
</script>
</x-layouts.app>