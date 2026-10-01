@php
    $statusLabel = [
        'mendatang' => ['Mendatang', 'bg-brand-100 text-brand-600'],
        'selesai' => ['Selesai', 'bg-slate-100 text-slate-500'],
        'menunggu_pembatalan' => ['Menunggu Pembatalan', 'bg-amber-100 text-amber-600'],
        'dibatalkan' => ['Dibatalkan', 'bg-busy-bg text-busy-text'],
    ];
@endphp

<x-layouts.app title="Dashboard">
    <h1 class="text-2xl font-extrabold text-slate-900">Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
    <p class="mt-1 text-slate-500">Berikut ringkasan reservasi aktif dan riwayat aktivitas Anda.</p>

    {{-- Reservasi hari ini --}}
    <div class="mt-8 flex items-center justify-between">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
            Reservasi saat ini / yang akan datang
            @if ($todayReservations->isNotEmpty())
                <span class="ml-1 font-normal text-slate-400">({{ $todayReservations->count() }})</span>
            @endif
        </h2>
        {{-- <a href="{{ route('my-reservations.index') }}" class="text-sm font-semibold text-brand-500 hover:underline">Lihat Semua Reservasi &rarr;</a> --}}
    </div>

    {{-- Mengunci tinggi tepat untuk 3 kartu menggunakan inline style --}}
    <div class="mt-3 space-y-3 overflow-y-auto pr-1" style="max-height: 275px;">
        @forelse ($todayReservations as $r)
            @php $isRoom = $r['jenis'] === 'room'; @endphp

            <div class="flex flex-col gap-4 rounded-xl bg-white p-4 shadow-sm shadow-slate-200/60 sm:flex-row sm:items-center sm:justify-between {{ $isRoom ? 'border-l-4 border-brand-500' : 'border-l-4 border-violet-500' }}">
                <div class="flex items-center gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $isRoom ? 'bg-brand-50 text-brand-500' : 'bg-violet-50 text-violet-500' }}">
                        @if ($isRoom)
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><rect x="4" y="5.5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 10h16" stroke="currentColor" stroke-width="1.6"/></svg>
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><rect x="3" y="7" width="13" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="m16.5 10 4-2.5v9l-4-2.5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                        @endif
                    </span>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-bold text-slate-900">{{ $r['ruangan'] }}</p>
                            <span class="text-xs font-normal text-slate-400">• Dibuat {{ $r['created_at_label'] }}</span>
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                            <span class="inline-flex items-center gap-1">Tanggal: {{ $r['tanggal_lengkap'] }}</span>
                            <span class="inline-flex items-center gap-1">Waktu: {{ $r['waktu'] }}</span>
                            <span class="inline-flex items-center gap-1">Keperluan: {{ $r['keperluan'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusLabel[$r['status']][1] }}">{{ $statusLabel[$r['status']][0] }}</span>
                    <button
                        type="button"
                        onclick="openReservationDetail(@js($r['detail']))"
                        class="rounded-lg border border-slate-200 px-3.5 py-1.5 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                    >
                        Lihat Detail
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-brand-100 bg-white px-5 py-8 text-center text-sm text-slate-400">
                Tidak ada reservasi untuk hari ini.
            </div>
        @endforelse
    </div>

    {{-- Reservasi Saya --}}
<div class="mt-10">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                Reservasi Saya
            </h2>
        </div>
    </div>

    {{-- Filter Reservasi --}}
    <form method="GET" action="{{ route('dashboard') }}"
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
                        Ruang Rapat
                    </option>
                    <option value="zoom" @selected(request('jenis') === 'zoom')>
                        Breakout Room Zoom
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

    {{-- Tabel --}}
    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Jenis</th>
                        <th class="p-3">Ruangan / Tempat</th>
                        <th class="p-3">Keperluan</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($reservations as $r)
                        <tr class="transition hover:bg-slate-50">
                            <td class="p-3 font-mono text-xs font-bold text-brand-600">
                                {{ $r['id'] }}
                            </td>

                            <td class="p-3">
                                <span class="rounded px-2 py-1 text-xs font-semibold
                                    {{ match($r['jenis']) {
                                        'room' => 'bg-blue-100 text-blue-800',
                                        'zoom' => 'bg-purple-100 text-purple-800',
                                        'form' => 'bg-emerald-100 text-emerald-800',
                                        default => 'bg-slate-100 text-slate-800',
                                    } }}">
                                    {{ $r['jenis_label'] }}
                                </span>
                            </td>

                            <td class="p-3 font-semibold">
                                {{ $r['ruangan'] }}
                            </td>

                            <td class="max-w-[200px] truncate p-3">
                                {{ $r['keperluan'] }}
                            </td>

                            <td class="whitespace-nowrap p-3">
                                {{ $r['tanggal'] }}
                            </td>

                            <td class="whitespace-nowrap p-3">
                                {{ $r['waktu'] }}
                            </td>

                            <td class="p-3">
                                @php
                                    $statusLabels = [
                                        'mendatang' => ['Mendatang', 'bg-blue-100 text-blue-700'],
                                        'selesai' => ['Selesai', 'bg-gray-100 text-gray-700'],
                                        'menunggu_pembatalan' => ['Menunggu Pembatalan', 'bg-amber-100 text-amber-700'],
                                        'dibatalkan' => ['Dibatalkan', 'bg-red-100 text-red-700'],
                                    ];

                                    [$label, $statusClass] = $statusLabels[$r['status']]
                                        ?? [$r['status'], 'bg-gray-100 text-gray-700'];
                                @endphp

                                <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $label }}
                                </span>
                            </td>

                                <td class="p-3">
                                    <div class="relative inline-block text-left" x-data="{ open: false }">
                                        <button type="button" 
                                                @click="open = !open"
                                                @click.outside="open = false"
                                                class="flex items-center gap-1 rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                            Aksi
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                                <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>

                                        {{-- Dropdown Menu (Menempel tepat di bawah tombol Aksi) --}}
                                        <div x-show="open"
                                            x-cloak
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute right-0 z-50 mt-1 w-48 divide-y divide-slate-100 rounded-xl border border-slate-100 bg-white py-1.5 text-left text-xs shadow-xl">
                                            
                                            <div class="py-1">
                                                @if ($r['jenis'] === 'form')
                                                    <button type="button"
                                                            @click="open = false"
                                                            onclick="openReservationDetail(@js($r['detail']))"
                                                            class="block w-full px-4 py-2 text-left hover:bg-brand-50 hover:text-brand-600">
                                                        Lihat Detail
                                                    </button>
                                                    <a href="{{ $r['detail_url'] }}"
                                                    class="block w-full px-4 py-2 text-left hover:bg-brand-50 hover:text-brand-600">
                                                        Lihat Respons
                                                    </a>
                                                    
                                                @else
                                                    <button type="button"
                                                            @click="open = false"
                                                            onclick="openReservationDetail(@js($r['detail']))"
                                                            class="block w-full px-4 py-2 text-left hover:bg-brand-50 hover:text-brand-600">
                                                        Lihat Detail
                                                    </button>
                                                @endif
                                            </div>

                                            @if ($r['can_cancel'] && $r['status'] === 'mendatang')
                                                <div class="py-1">
                                                    <button type="button"
                                                            @click="open = false"
                                                            onclick="openCancelConfirm('{{ $r['jenis'] }}', {{ $r['raw_id'] }}, @js($r['id']))"
                                                            class="block w-full px-4 py-2 text-left text-red-600 hover:bg-red-50">
                                                        Ajukan Pembatalan
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-sm text-slate-400">
                                Tidak ada reservasi yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($reservations->hasPages())
            <div class="border-t border-slate-100 px-4 py-4">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>

    <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
        <span>
            Menampilkan {{ $reservations->firstItem() ?? 0 }}
            sampai {{ $reservations->lastItem() ?? 0 }}
            dari {{ $reservations->total() }} reservasi
        </span>
        <span>10 reservasi per halaman</span>
    </div>
</div>
{{-- Modal Konfirmasi Ajukan Pembatalan --}}
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

        <h3 id="cancelConfirmTitle" class="mt-4 text-lg font-bold text-slate-900">Ajukan Pembatalan?</h3>
        <p id="cancelConfirmText" class="mt-1.5 text-sm text-slate-500">
            Reservasi akan diajukan untuk dibatalkan dan menunggu persetujuan Admin.
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
                Ya, Ajukan Pembatalan
            </button>
        </div>
    </div>
</div>

<form
    id="cancelConfirmForm"
    action="{{ route('reservations.request-cancel') }}"
    method="POST"
    class="hidden"
>
    @csrf
    <input type="hidden" name="type" id="cancelConfirmType">
    <input type="hidden" name="id" id="cancelConfirmId">
</form>

{{-- Modal Detail Reservasi (bersama dengan Semua Pemesanan Admin) --}}
@include('partials.reservation-detail-modal')

<script>
    // Dipanggil modal detail bersama saat tombol "Ajukan Pembatalan" diklik.
    function reservationDetailCancel(res) {
        closeReservationDetail();
        openCancelConfirm(res.cancel.jenis, res.cancel.raw_id, res.kode);
    }

    // ------------------------------------------------------------------
    // Modal Konfirmasi Ajukan Pembatalan (menggantikan confirm() bawaan browser)
    // ------------------------------------------------------------------
    function openCancelConfirm(jenis, id, label) {
        document.getElementById('cancelConfirmType').value = jenis;
        document.getElementById('cancelConfirmId').value = id;
        document.getElementById('cancelConfirmText').textContent =
            `Reservasi ${label ?? ''} akan diajukan untuk dibatalkan dan menunggu persetujuan Admin.`;

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
        document.getElementById('cancelConfirmForm').submit();
    }

    // Tutup modal jika pengguna menekan tombol Escape.
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeCancelConfirm();
        }
    });
</script>
</x-layouts.app>
