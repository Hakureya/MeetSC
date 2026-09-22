<x-layouts.app title="Riwayat Pemesanan">
<div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Filter Bar -->
    <form method="GET" action="{{ route('reservations.index') }}"
          class="mt-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Pencarian --}}
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">
                    Cari Reservasi
                </label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="ID, ruangan, keperluan..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                >
            </div>

            {{-- Jenis Reservasi --}}
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">
                    Jenis Reservasi
                </label>
                <select
                    name="jenis"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500"
                >
                    <option value="">Semua Jenis</option>
                    <option value="room" @selected(request('jenis') === 'room')>
                        Ruang Meeting
                    </option>
                    <option value="zoom" @selected(request('jenis') === 'zoom')>
                        Zoom Meeting
                    </option>
                    <option value="form" @selected(request('jenis') === 'form')>
                        Form Kehadiran
                    </option>
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">
                    Status
                </label>
                <select
                    name="status"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500"
                >
                    <option value="">Semua Status</option>
                    <option value="mendatang" @selected(request('status') === 'mendatang')>
                        Mendatang
                    </option>
                    <option value="selesai" @selected(request('status') === 'selesai')>
                        Selesai
                    </option>
                    <option value="menunggu_pembatalan" @selected(request('status') === 'menunggu_pembatalan')>
                        Menunggu Pembatalan
                    </option>
                    <option value="dibatalkan" @selected(request('status') === 'dibatalkan')>
                        Dibatalkan
                    </option>
                </select>
            </div>

            {{-- Urutan --}}
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">
                    Urutkan
                </label>
                <select
                    name="sort"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500"
                >
                    <option value="terbaru" @selected(request('sort', 'terbaru') === 'terbaru')>
                        Terbaru
                    </option>
                    <option value="terlama" @selected(request('sort') === 'terlama')>
                        Terlama
                    </option>
                    <option value="tanggal_asc" @selected(request('sort') === 'tanggal_asc')>
                        Tanggal Terdekat
                    </option>
                    <option value="tanggal_desc" @selected(request('sort') === 'tanggal_desc')>
                        Tanggal Terjauh
                    </option>
                </select>
            </div>
        </div>

        {{-- Filter Tanggal --}}
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">
                    Tanggal Mulai
                </label>
                <input
                    type="date"
                    name="tanggal_mulai"
                    value="{{ request('tanggal_mulai') }}"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500"
                >
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">
                    Tanggal Akhir
                </label>
                <input
                    type="date"
                    name="tanggal_akhir"
                    value="{{ request('tanggal_akhir') }}"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500"
                >
            </div>

            <div class="flex items-end gap-2 lg:col-span-2">
                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600"
                >
                    Terapkan Filter
                </button>

                <a
                    href="{{ route('dashboard') }}"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                >
                    Reset
                </a>
            </div>
        </div>
    </form>
    <br>
    <!-- Tabel Reservasi -->
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b text-gray-600 uppercase text-[11px]">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Pemesan</th>
                        <th class="p-3">Jenis</th>
                        <th class="p-3">Ruangan / Tempat</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Keperluan</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-gray-700">
                    @forelse($reservations as $res)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 font-mono font-bold">{{ $res['id'] }}</td>
                            <td class="p-3">{{ $res['pemesan'] }}</td>
                            <td class="p-3">
                                @php
                                    $jenisClasses = [
                                        'Ruang' => 'bg-blue-100 text-blue-800',
                                        'Zoom' => 'bg-purple-100 text-purple-800',
                                        'Form' => 'bg-amber-100 text-amber-800',
                                    ];
                                    $jenisLabels = [
                                        'Ruang' => 'Ruang Rapat',
                                        'Zoom' => 'Breakout Room Zoom',
                                        'Form' => 'Form Kehadiran',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $jenisClasses[$res['jenis']] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $jenisLabels[$res['jenis']] ?? $res['jenis'] }}
                                </span>
                            </td>
                            <td class="p-3 font-semibold">{{ $res['ruang'] }}</td>
                            <td class="p-3">{{ $res['tanggal'] }}</td>
                            <td class="p-3">{{ $res['waktu'] }}</td>
                            <td class="p-3 truncate max-w-xs">{{ $res['keperluan'] }}</td>
                            <td class="p-3">
                                @php
                                    $statusLabels = [
                                        'mendatang' => ['Mendatang', 'bg-blue-100 text-blue-700'],
                                        'selesai' => ['Selesai', 'bg-gray-100 text-gray-700'],
                                        'menunggu_pembatalan' => ['Menunggu Pembatalan', 'bg-amber-100 text-amber-700'],
                                        'dibatalkan' => ['Dibatalkan', 'bg-red-100 text-red-700'],
                                    ];
                                    [$statusLabel, $statusClass] = $statusLabels[$res['status']] ?? [$res['status'], 'bg-gray-100 text-gray-700'];
                                @endphp
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <button onclick='openDetailModal(@json($res))' class="text-xs bg-emerald-50 text-emerald-700 px-3 py-1 rounded-md font-semibold hover:bg-emerald-100 transition-colors">Detail</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-gray-400">Tidak ada data reservasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($reservations->hasPages())
        <div class="mt-4">
            {{ $reservations->links() }}
        </div>
    @endif
</div>

{{-- Modal Detail Reservasi --}}
<div id="detailModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-3 sm:p-4">
    <!-- max-w-xl membatasi lebar agar pas dan tidak kelebaran -->
    <div class="bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[85vh] flex flex-col overflow-hidden border">
        
        {{-- Header Modal --}}
        <div class="flex justify-between items-center px-4 py-3 border-b bg-gray-50 shrink-0">
            <h4 id="modalTitle" class="font-bold text-gray-800 text-sm">Detail Reservasi</h4>
            <button type="button" onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>

        {{-- Body Modal (Scrollable jika layar pendek) --}}
        <div id="modalBody" class="px-4 py-3 overflow-y-auto flex-1 space-y-3">
            <!-- Konten diisi lewat JS -->
        </div>

        {{-- Footer Modal --}}
        <div id="modalActions" class="px-4 py-3 border-t bg-gray-50 shrink-0">
            <!-- Action button diisi lewat JS -->
        </div>

    </div>
</div>

<script>
const csrfToken = '{{ csrf_token() }}';

function statusLabel(status) {
    const labels = {
        mendatang: 'Mendatang',
        selesai: 'Selesai',
        menunggu_pembatalan: 'Menunggu Pembatalan',
        dibatalkan: 'Dibatalkan',
    };
    return labels[status] || status;
}

function openDetailModal(res) {
    const user = res.user || {};

    document.getElementById('modalTitle').innerText = `Detail Reservasi ${res.id}`;

    const row = (label, value) => {
        if (value === null || value === undefined || value === '') return '';
        return `
            <div class="flex justify-between gap-2 py-1 border-b border-gray-100 last:border-0 text-[11px]">
                <span class="text-gray-500 font-medium shrink-0">${label}</span>
                <span class="font-semibold text-gray-800 text-right truncate">${value}</span>
            </div>
        `;
    };

    const section = (title, rowsHtml) => {
        const filled = rowsHtml.filter(Boolean).join('');
        if (!filled) return '';
        return `
            <div class="rounded-lg border border-gray-200 bg-white p-2.5 shadow-xs flex flex-col justify-between">
                <div>
                    <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600">${title}</p>
                    <div class="space-y-0.5">${filled}</div>
                </div>
            </div>
        `;
    };

    const sections = [
        section('Informasi Pemesan', [
            row('Nama', user.name),
            row('NIP', user.nip || '-'),
            row('Divisi', res.divisi_pemesan || user.divisi || '-'),
            row('Jabatan', user.jabatan || 'Staff'),
            row('Email', res.email_pemesan)
        ]),

        section('Informasi Reservasi', [
            row('Kode', res.id),
            row(
                'Jenis',
                res.jenis === 'Ruang'
                    ? 'Ruang Rapat'
                    : res.jenis === 'Zoom'
                    ? 'Breakout Room Zoom'
                    : res.jenis === 'Form'
                    ? 'Form Kehadiran'
                    : '-' // Nilai bawaan (default) jika tidak ada yang cocok
                ),
            row('Ruangan', res.ruang),
            row('Lantai', res.lantai),
            row('Kapasitas', res.kapasitas),
            row('Ruang Gabungan', res.ruang_gabungan),
            row('Tanggal', res.tanggal_lengkap || res.tanggal),
            row('Waktu', res.waktu),
            row('Peserta', res.jumlah_peserta ? `${res.jumlah_peserta} orang` : null),
            row('Konsumsi', res.konsumsi),
            row('Keperluan', res.keperluan)
        ]),

        section('Penanggung Jawab', [
            row('Nama PIC', res.nama_pic || '-'),
            row('No. Telp PIC', res.no_telp_pic),
            row('Divisi PIC', res.divisi_pic)
        ]),

        section('Pemesanan', [
            row('Status', statusLabel(res.status)),
            row('Dibuat', res.dibuat)
        ])
    ];

    // Broadcast & Link Zoom — hanya untuk reservasi Breakout Room Zoom, supaya
    // Admin bisa melihat/menyalin lagi teks broadcast + link Zoom dari riwayat.
    const escapeHtml = (str) => String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    const broadcastBlock = (res.jenis === 'Zoom' && res.broadcast) ? `
        <div class="rounded-lg border border-gray-200 bg-white p-2.5 shadow-xs sm:col-span-2">
            <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600">Broadcast &amp; Link Zoom</p>
            <div id="modalBroadcastText" class="whitespace-pre-wrap rounded-md border border-gray-100 bg-gray-50 p-2 font-mono text-[11px] leading-relaxed text-gray-700 select-all">${escapeHtml(res.broadcast)}</div>
            <button
                type="button"
                onclick="copyAdminBroadcast()"
                id="adminCopyBroadcastBtn"
                class="mt-2 w-full rounded-lg bg-emerald-600 py-2 text-xs font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
            >
                Salin Broadcast
            </button>
        </div>
    ` : '';

    document.getElementById('modalBody').innerHTML = `
        <div class="mb-2">
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                ${statusLabel(res.status)}
            </span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            ${sections.join('')}
            ${broadcastBlock}
        </div>
    `;

    const actionContainer = document.getElementById('modalActions');
    actionContainer.innerHTML = '';

    // Reservasi berstatus "selesai" atau "dibatalkan" tidak bisa dibatalkan lagi,
    // begitu pula pemesanan yang memang tidak punya mekanisme pembatalan (Form Kehadiran).
    const canCancel = res.can_cancel && res.status !== 'dibatalkan' && res.status !== 'selesai';

    // Tombol menuju link Zoom — hanya untuk reservasi Breakout Room Zoom.
    const zoomButtonHtml = (res.jenis === 'Zoom' && res.zoom_link) ? `
        <a
            href="${escapeHtml(res.zoom_link)}"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full block text-center py-2 border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg font-bold text-xs transition-colors"
        >
            Menuju Link Zoom
        </a>
    ` : '';

    const cancelFormHtml = canCancel ? `
        <form action="/semua-pemesanan/batalkan/${res.jenis}/${res.raw_id}" method="POST" class="w-full">
            <input type="hidden" name="_token" value="${csrfToken}">
            <button 
                type="submit" 
                onclick="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')"
                class="w-full py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold text-xs transition-colors shadow-sm"
            >
                Batalkan Reservasi
            </button>
        </form>
    ` : '';

    if (zoomButtonHtml || cancelFormHtml) {
        actionContainer.innerHTML = `
            <div class="flex flex-col gap-2">
                ${zoomButtonHtml}
                ${cancelFormHtml}
            </div>
        `;
    }

    const modal = document.getElementById('detailModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// ------------------------------------------------------------------
// Salin Broadcast Zoom (dari modal Detail Reservasi Admin)
// ------------------------------------------------------------------
function copyAdminBroadcast() {
    const el = document.getElementById('modalBroadcastText');
    const btn = document.getElementById('adminCopyBroadcastBtn');
    if (!el || !btn) return;

    const text = el.textContent;

    function markCopied() {
        btn.innerText = 'Berhasil Disalin!';
        setTimeout(() => { btn.innerText = 'Salin Broadcast'; }, 2000);
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(markCopied).catch(() => copyAdminBroadcastFallback(text, markCopied));
    } else {
        copyAdminBroadcastFallback(text, markCopied);
    }
}

function copyAdminBroadcastFallback(text, onSuccess) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
        if (document.execCommand('copy')) {
            onSuccess();
        } else {
            alert('Gagal menyalin otomatis. Silakan salin teks di atas secara manual.');
        }
    } catch (e) {
        alert('Gagal menyalin otomatis. Silakan salin teks di atas secara manual.');
    }
    document.body.removeChild(textarea);
}

document.getElementById('detailModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeDetailModal();
    }
});
</script>
</x-layouts.app>