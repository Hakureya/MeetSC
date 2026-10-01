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
                    <option value="Ruang" @selected(request('jenis') === 'Ruang')>
                        Ruang Rapat
                    </option>
                    <option value="Zoom" @selected(request('jenis') === 'Zoom')>
                        Breakout Room Zoom
                    </option>
                    <option value="Form" @selected(request('jenis') === 'Form')>
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
                    href="{{ route('reservations.index') }}"
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
                        <th class="p-3">Keperluan</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Waktu</th>
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
                            <td class="p-3 truncate max-w-xs">{{ $res['keperluan'] }}</td>
                            <td class="p-3">{{ $res['tanggal'] }}</td>
                            <td class="p-3">{{ $res['waktu'] }}</td>
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
                                <button type="button" onclick="openReservationDetail(@js($res['detail']))" class="text-xs bg-emerald-50 text-emerald-700 px-3 py-1 rounded-md font-semibold hover:bg-emerald-100 transition-colors">Detail</button>
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

{{-- Modal Detail Reservasi (bersama dengan Dashboard) --}}
@include('partials.reservation-detail-modal')

{{-- Modal Konfirmasi Pembatalan oleh Admin (menggantikan confirm() bawaan browser) --}}
<div
    id="cancelConfirmModal"
    class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cancelConfirmTitle"
>
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>

        <h3 id="cancelConfirmTitle" class="mt-4 text-lg font-bold text-slate-900">Batalkan Reservasi?</h3>
        <p id="cancelConfirmText" class="mt-1.5 text-sm text-slate-500">
            Reservasi akan dibatalkan.
        </p>

        <div class="mt-6 flex gap-3">
            <button
                type="button"
                onclick="closeCancelConfirm()"
                class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50"
            >
                Batal
            </button>
            <button
                type="button"
                onclick="submitCancelConfirm()"
                class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700"
            >
                Ya, Batalkan Reservasi
            </button>
        </div>
    </div>
</div>

{{-- Form pembatalan oleh Admin, dikirim dari modal konfirmasi di atas. --}}
<form id="adminCancelForm" method="POST" class="hidden">
    @csrf
</form>

<script>
    // Dipanggil modal detail bersama saat tombol "Batalkan Reservasi" diklik.
    function reservationDetailCancel(res) {
        closeReservationDetail();

        const jenis = res.cancel.jenis === 'room' ? 'Ruang' : 'Zoom';
        document.getElementById('adminCancelForm').action =
            `/semua-pemesanan/batalkan/${jenis}/${res.cancel.raw_id}`;
        document.getElementById('cancelConfirmText').textContent =
            `Reservasi ${res.kode ?? ''} akan dibatalkan.`;

        const modal = document.getElementById('cancelConfirmModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCancelConfirm() {
        const modal = document.getElementById('cancelConfirmModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function submitCancelConfirm() {
        document.getElementById('adminCancelForm').submit();
    }

    document.getElementById('cancelConfirmModal').addEventListener('click', function (event) {
        if (event.target === this) closeCancelConfirm();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeCancelConfirm();
    });
</script>
</x-layouts.app>