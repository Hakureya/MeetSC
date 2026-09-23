<x-layouts.app title="Form Kehadiran">
<div class="max-w-7xl mx-auto py-6 px-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Kolom Kiri: Attendance Builder -->
        <div class="lg:col-span-2 bg-white border rounded-2xl p-6 shadow-sm lg:order-1">
            <h3 class="font-bold text-gray-800 mb-4 text-base">Buat Formulir Kehadiran</h3>
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Judul Form <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="judul" value="{{ old('judul') }}" required placeholder="Masukkan Judul Absen" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Batas Waktu Pengisian (Kedaluwarsa) <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                            PIC <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="pic" value="{{ old('pic') }}" required placeholder="Nama penanggung jawab" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                            Divisi PIC <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="divisi_pic" value="{{ old('divisi_pic') }}" required placeholder="Contoh: Renbis" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Tempat <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="tempat" value="{{ old('tempat') }}" required placeholder="Contoh: Ruang Adaptif, Lantai 2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Rapat/Pertemuan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="rapat_pertemuan" value="{{ old('rapat_pertemuan') }}" required placeholder="Contoh: Rapat Koordinasi Mingguan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Field Isian Form</label>
                        <button type="button" onclick="addField()" class="text-xs text-emerald-600 font-bold hover:text-emerald-700">+ Tambah Field</button>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-2">Field di bawah ini adalah isian yang akan diisi sendiri oleh peserta saat membuka tautan formulir.</p>
                    <div id="fieldContainer" class="max-h-80 space-y-3 overflow-y-auto pr-1"></div>
                </div>
                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow transition">Terbitkan Formulir & Buat Link</button>
            </form>
        </div>
        <!-- Kolom Kanan: Riwayat / Daftar Form -->
        <div class="bg-white border rounded-2xl p-5 shadow-sm lg:order-2">
            <h3 class="font-bold text-gray-800 mb-4 text-base">
                @if (auth()->user()->role === 'admin')
                    Seluruh Form Pengguna
                @else
                    Riwayat Form Kehadiran Saya
                @endif
            </h3>
            <div class="space-y-3">
                @php
                    $publicLinkBase = 'http://192.168.83.14:8000/hadir/';
                @endphp
                @forelse ($forms as $f)
                    <div class="p-3.5 border rounded-xl hover:border-emerald-500 transition bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold text-gray-900 text-sm">{{ $f->judul }}</h4>
                                @if (auth()->user()->role === 'admin')
                                    <p class="text-[11px] text-gray-400">Pembuat: {{ $f->user->name ?? '-' }}</p>
                                @endif
                                <p class="text-[11px] text-gray-500 mt-1">
                                    {{ $f->rapat_pertemuan }} &bull; {{ $f->tempat }} &bull; PIC: {{ $f->pic }} ({{ $f->divisi_pic }})
                                </p>
                                <p class="text-[11px] text-gray-500 mt-1">
                                    Kadaluarsa: {{ \Carbon\Carbon::parse($f->expires_at)->isoFormat('D MMM Y, HH:mm') }}
                                </p>
                            </div>
                            @if (now()->greaterThan($f->expires_at))
                                <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[10px] font-bold rounded">Kedaluwarsa</span>
                            @else
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[10px] font-bold rounded">Aktif</span>
                            @endif
                        </div>
                        <div class="mt-3 flex gap-2 border-t pt-2.5">
                            <button type="button" onclick="copyLink('{{ $publicLinkBase . $f->uuid }}')" class="px-2.5 py-1 text-xs bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100">Salin Link</button>
                            <a href="{{ route('attendance.show', $f->id) }}" class="px-2.5 py-1 text-xs bg-emerald-50 text-emerald-700 rounded-lg font-semibold hover:bg-emerald-100">Lihat Jawaban</a>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-6">Belum ada formulir kehadiran.</p>
                @endforelse
            </div>
            @if ($forms->hasPages())
                <div class="mt-4 border-t pt-3">
                    {{ $forms->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Custom Alert (pengganti alert() bawaan browser) -->
<div id="customAlertModal" class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div id="customAlertBox" class="w-full max-w-xs rounded-xl bg-emerald-600 p-4 shadow-2xl">
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
let fieldIdx = 0;
function addField(label = '', type = 'text', req = false) {
    const container = document.getElementById('fieldContainer');
    const div = document.createElement('div');
    div.className = "flex flex-wrap sm:flex-nowrap gap-2 items-center bg-gray-50 border p-3 rounded-xl";
    div.innerHTML = `
        <input type="text" name="fields[${fieldIdx}][nama]" value="${label}" placeholder="Nama Field (contoh: No. Telp)" required class="flex-1 text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        <select name="fields[${fieldIdx}][tipe]" class="text-sm rounded-lg border border-slate-300 px-3 py-2 text-slate-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            <option value="text" ${type === 'text' ? 'selected' : ''}>Teks</option>
            <option value="number" ${type === 'number' ? 'selected' : ''}>Angka</option>
            <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
            <option value="date" ${type === 'date' ? 'selected' : ''}>Tanggal</option>
            <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Teks Panjang</option>
        </select>
        <label class="flex items-center gap-1 text-xs text-gray-600 px-2 cursor-pointer">
            <input type="checkbox" name="fields[${fieldIdx}][required]" value="1" ${req ? 'checked' : ''} class="rounded text-emerald-600 focus:ring-emerald-500"> Wajib
        </label>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-xs px-2 py-1 font-semibold">Hapus</button>
    `;
    container.appendChild(div);
    fieldIdx++;
}
addField('Nama Lengkap', 'text', true);
addField('NIP / NID', 'text', true);

// ===== Modal Alert Custom (pengganti alert() bawaan) =====
const customAlertModal = document.getElementById('customAlertModal');
const customAlertBox = document.getElementById('customAlertBox');
const customAlertIconWrap = document.getElementById('customAlertIconWrap');
const customAlertTitle = document.getElementById('customAlertTitle');
const customAlertMessage = document.getElementById('customAlertMessage');
const customAlertOkBtn = document.getElementById('customAlertOkBtn');

function showCustomAlert(message, { title = 'Pemberitahuan', type = 'success' } = {}) {
    const styles = {
        success: { box: 'bg-emerald-600' },
        danger:  { box: 'bg-red-600' },
        warning: { box: 'bg-amber-500' },
        info:    { box: 'bg-blue-600' },
    };
    const style = styles[type] || styles.success;
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

function copyLink(url) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url)
            .then(() => showCustomAlert('Link formulir berhasil disalin!', { title: 'Berhasil', type: 'success' }))
            .catch(() => copyLinkFallback(url));
    } else {
        copyLinkFallback(url);
    }
}

function copyLinkFallback(url) {
    const textarea = document.createElement('textarea');
    textarea.value = url;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
        const success = document.execCommand('copy');
        if (success) {
            showCustomAlert('Link formulir berhasil disalin!', { title: 'Berhasil', type: 'success' });
        } else {
            showCustomAlert(`Gagal menyalin link secara otomatis. Salin manual:<br><span class="font-mono break-all">${url}</span>`, { title: 'Gagal Menyalin', type: 'danger' });
        }
    } catch (e) {
        showCustomAlert(`Gagal menyalin link secara otomatis. Salin manual:<br><span class="font-mono break-all">${url}</span>`, { title: 'Gagal Menyalin', type: 'danger' });
    }
    document.body.removeChild(textarea);
}
</script>
</x-layouts.app>