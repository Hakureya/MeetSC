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
                        onclick="openDashboardDetail(@js([
                            'id' => $r['id'],
                            'jenis' => $r['jenis_label'],
                            'jenis_raw' => $r['jenis'],
                            'ruang' => $r['ruangan'],
                            'tanggal' => $r['tanggal_lengkap'],
                            'waktu' => $r['waktu'],
                            'keperluan' => $r['keperluan'],
                            'status' => $r['status'],
                            'jumlah_peserta' => $r['jumlah_peserta'],
                            'konsumsi' => $r['konsumsi'],
                            'nama_pic' => $r['nama_pic'],
                            'no_telp_pic' => $r['no_telp_pic'],
                            'divisi_pic' => $r['divisi_pic'],
                            'created_at' => $r['created_at_label'],
                        ]))"
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
                        <th class="p-3">Ruangan / Agenda</th>
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
                                                            onclick="openFormDetailModal(@js([
                                                                'id' => $r['id'],
                                                                'judul' => $r['ruangan'],
                                                                'pic' => $r['form_pic'],
                                                                'divisi_pic' => $r['form_divisi_pic'],
                                                                'tempat' => $r['form_tempat'],
                                                                'rapat' => $r['form_rapat'],
                                                                'expires_label' => $r['form_expires_label'],
                                                                'status' => $r['status'],
                                                                'created_at' => $r['created_at_label'],
                                                                'link' => $r['form_link'],
                                                            ]))"
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
                                                            onclick="openDashboardDetail(@js([
                                                                'id' => $r['id'],
                                                                'jenis' => $r['jenis_label'],
                                                                'jenis_raw' => $r['jenis'],
                                                                'ruang' => $r['ruangan'],
                                                                'tanggal' => $r['tanggal_lengkap'],
                                                                'waktu' => $r['waktu'],
                                                                'keperluan' => $r['keperluan'],
                                                                'status' => $r['status'],
                                                                'jumlah_peserta' => $r['jumlah_peserta'],
                                                                'konsumsi' => $r['konsumsi'],
                                                                'nama_pic' => $r['nama_pic'],
                                                                'no_telp_pic' => $r['no_telp_pic'],
                                                                'divisi_pic' => $r['divisi_pic'],
                                                                'created_at' => $r['created_at_label'],
                                                            ]))"
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

{{-- Modal Detail Reservasi Dashboard --}}
<div
    id="dashboardDetailModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="dashboardModalTitle"
>
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 id="dashboardModalTitle" class="text-lg font-bold text-slate-900">
                    Detail Reservasi
                </h3>
                <p id="dashboardModalId" class="mt-1 text-xs text-slate-400"></p>
            </div>

            <button
                type="button"
                onclick="closeDashboardDetail()"
                class="text-2xl text-slate-400 hover:text-slate-600"
                aria-label="Tutup detail"
            >
                &times;
            </button>
        </div>

        {{-- Status --}}
        <div class="py-4">
            <span
                id="dashboardModalStatus"
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
            ></span>
        </div>

        {{-- Informasi Reservasi --}}
        <div class="space-y-4 text-sm">

            <div>
                <h4 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">
                    Informasi Reservasi
                </h4>

                <div class="divide-y divide-slate-100 rounded-lg border border-slate-100">
                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Jenis</span>
                        <span id="dashboardModalJenis" class="text-right font-semibold text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Ruangan / Platform</span>
                        <span id="dashboardModalRuang" class="text-right font-semibold text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Tanggal</span>
                        <span id="dashboardModalTanggal" class="text-right text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Waktu</span>
                        <span id="dashboardModalWaktu" class="text-right text-slate-800"></span>
                    </div>

                    {{-- Baris ini hanya relevan untuk Ruang Rapat, disembunyikan untuk Zoom via JS --}}
                    <div id="dashboardRowPeserta" class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Jumlah Peserta</span>
                        <span id="dashboardModalPeserta" class="text-right text-slate-800"></span>
                    </div>

                    <div id="dashboardRowKonsumsi" class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Konsumsi</span>
                        <span id="dashboardModalKonsumsi" class="text-right text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Nama PIC</span>
                        <span id="dashboardModalPic" class="text-right text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">No. Telp PIC</span>
                        <span id="dashboardModalTelpPic" class="text-right text-slate-800"></span>
                    </div>

                    {{-- Divisi PIC berlaku untuk reservasi Ruang Rapat maupun Breakout Room Zoom --}}
                    <div id="dashboardRowDivisiPic" class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Divisi PIC</span>
                        <span id="dashboardModalDivisiPic" class="text-right text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Keperluan / Agenda</span>
                        <span id="dashboardModalKeperluan" class="max-w-[60%] text-right text-slate-800"></span>
                    </div>
                </div>
            </div>

            {{-- Informasi Pembuatan --}}
            <div>
                <h4 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">
                    Informasi Pemesanan
                </h4>

                <div class="flex justify-between gap-4 rounded-lg border border-slate-100 p-3 text-sm">
                    <span class="text-slate-500">Dibuat pada</span>
                    <span id="dashboardModalCreated" class="text-right text-slate-800"></span>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex justify-end border-t border-slate-100 pt-4">
            <button
                type="button"
                onclick="closeDashboardDetail()"
                class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200"
            >
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- Modal Detail Pembuatan Form Kehadiran --}}
<div
    id="dashboardFormDetailModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="formModalTitle"
>
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 id="formModalTitle" class="text-lg font-bold text-slate-900">
                    Detail Pembuatan Form
                </h3>
                <p id="formModalId" class="mt-1 text-xs text-slate-400"></p>
            </div>

            <button
                type="button"
                onclick="closeFormDetailModal()"
                class="text-2xl text-slate-400 hover:text-slate-600"
                aria-label="Tutup detail"
            >
                &times;
            </button>
        </div>

        {{-- Status --}}
        <div class="py-4">
            <span
                id="formModalStatus"
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
            ></span>
        </div>

        {{-- Informasi Pembuatan Form --}}
        <div class="space-y-4 text-sm">
            <div>
                <h4 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">
                    Informasi Form
                </h4>

                <div class="divide-y divide-slate-100 rounded-lg border border-slate-100">
                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Judul Form</span>
                        <span id="formModalJudul" class="max-w-[60%] text-right font-semibold text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">PIC</span>
                        <span id="formModalPic" class="text-right text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Divisi PIC</span>
                        <span id="formModalDivisiPic" class="text-right text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Tempat</span>
                        <span id="formModalTempat" class="max-w-[60%] text-right text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Rapat / Pertemuan</span>
                        <span id="formModalRapat" class="max-w-[60%] text-right text-slate-800"></span>
                    </div>

                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Kedaluwarsa</span>
                        <span id="formModalExpires" class="text-right text-slate-800"></span>
                    </div>
                </div>
            </div>

            {{-- Informasi Pembuatan --}}
            <div>
                <h4 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">
                    Informasi Pemesanan
                </h4>

                <div class="divide-y divide-slate-100 rounded-lg border border-slate-100">
                    <div class="flex justify-between gap-4 p-3">
                        <span class="text-slate-500">Dibuat pada</span>
                        <span id="formModalCreated" class="text-right text-slate-800"></span>
                    </div>

                    <div class="flex items-center justify-between gap-4 p-3">
                        <span class="text-slate-500">Link Formulir</span>
                        <button
                            type="button"
                            id="formModalCopyLink"
                            onclick="copyFormDetailLink()"
                            class="max-w-[60%] truncate text-right font-semibold text-brand-600 hover:underline"
                        ></button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex justify-end border-t border-slate-100 pt-4">
            <button
                type="button"
                onclick="closeFormDetailModal()"
                class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200"
            >
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function dashboardSetText(id, value) {
        document.getElementById(id).textContent = value ?? '-';
    }

    function openDashboardDetail(res) {
        dashboardSetText('dashboardModalTitle', 'Detail Reservasi');
        dashboardSetText('dashboardModalId', res.id);

        dashboardSetText('dashboardModalJenis', res.jenis);
        dashboardSetText('dashboardModalRuang', res.ruang);
        dashboardSetText('dashboardModalTanggal', res.tanggal);
        dashboardSetText('dashboardModalWaktu', res.waktu);

        // Jumlah Peserta & Konsumsi hanya relevan untuk Ruang Rapat.
        // Untuk Zoom, kedua baris ini disembunyikan supaya tidak menampilkan "-".
        const isRoom = res.jenis_raw === 'room';

        const rowPeserta = document.getElementById('dashboardRowPeserta');
        const rowKonsumsi = document.getElementById('dashboardRowKonsumsi');

        rowPeserta.classList.toggle('hidden', !isRoom);
        rowKonsumsi.classList.toggle('hidden', !isRoom);

        if (isRoom) {
            dashboardSetText(
                'dashboardModalPeserta',
                res.jumlah_peserta ? res.jumlah_peserta + ' orang' : '-'
            );
            dashboardSetText('dashboardModalKonsumsi', res.konsumsi);
        }

        dashboardSetText('dashboardModalPic', res.nama_pic);
        dashboardSetText('dashboardModalTelpPic', res.no_telp_pic);

        // Divisi PIC kini terisi untuk reservasi Ruang Rapat maupun Breakout Room Zoom.
        dashboardSetText('dashboardModalDivisiPic', res.divisi_pic);
        dashboardSetText('dashboardModalKeperluan', res.keperluan);
        dashboardSetText('dashboardModalCreated', res.created_at);

        const status = document.getElementById('dashboardModalStatus');

        const statusLabels = {
            mendatang: 'Mendatang',
            selesai: 'Selesai',
            menunggu_pembatalan: 'Menunggu Pembatalan',
            dibatalkan: 'Dibatalkan',
        };

        const statusClasses = {
            mendatang: 'bg-blue-100 text-blue-700',
            selesai: 'bg-gray-100 text-gray-700',
            menunggu_pembatalan: 'bg-amber-100 text-amber-700',
            dibatalkan: 'bg-red-100 text-red-700',
        };

        status.textContent = statusLabels[res.status] ?? res.status;

        status.className =
            'inline-flex rounded-full px-3 py-1 text-xs font-semibold ' +
            (statusClasses[res.status] ?? 'bg-gray-100 text-gray-700');

        const modal = document.getElementById('dashboardDetailModal');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDashboardDetail() {
        const modal = document.getElementById('dashboardDetailModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // ------------------------------------------------------------------
    // Modal Detail Pembuatan Form Kehadiran
    // ------------------------------------------------------------------
    function openFormDetailModal(res) {
        dashboardSetText('formModalId', res.id);
        dashboardSetText('formModalJudul', res.judul);
        dashboardSetText('formModalPic', res.pic);
        dashboardSetText('formModalDivisiPic', res.divisi_pic);
        dashboardSetText('formModalTempat', res.tempat);
        dashboardSetText('formModalRapat', res.rapat);
        dashboardSetText('formModalExpires', res.expires_label);
        dashboardSetText('formModalCreated', res.created_at);

        const linkBtn = document.getElementById('formModalCopyLink');
        linkBtn.textContent = res.link ?? '-';
        linkBtn.dataset.link = res.link ?? '';

        const status = document.getElementById('formModalStatus');

        const statusLabels = {
            mendatang: 'Formulir Aktif',
            selesai: 'Sudah Kedaluwarsa',
        };

        const statusClasses = {
            mendatang: 'bg-emerald-100 text-emerald-700',
            selesai: 'bg-slate-100 text-slate-600',
        };

        status.textContent = statusLabels[res.status] ?? res.status;

        status.className =
            'inline-flex rounded-full px-3 py-1 text-xs font-semibold ' +
            (statusClasses[res.status] ?? 'bg-gray-100 text-gray-700');

        const modal = document.getElementById('dashboardFormDetailModal');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeFormDetailModal() {
        const modal = document.getElementById('dashboardFormDetailModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

        function copyFormDetailLink() {
        const link = document.getElementById('formModalCopyLink').dataset.link;

        if (!link) return;

        // navigator.clipboard hanya tersedia di HTTPS atau localhost/127.0.0.1.
        // Saat diakses lewat IP LAN via HTTP biasa, ini bisa undefined dan gagal diam-diam.
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(link)
                .then(() => alert('Link formulir berhasil disalin!'))
                .catch(() => copyFormDetailLinkFallback(link));
        } else {
            copyFormDetailLinkFallback(link);
        }
    }

    function copyFormDetailLinkFallback(link) {
        const textarea = document.createElement('textarea');
        textarea.value = link;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            const success = document.execCommand('copy');
            alert(success ? 'Link formulir berhasil disalin!' : 'Gagal menyalin link. Salin manual: ' + link);
        } catch (e) {
            alert('Gagal menyalin link. Salin manual: ' + link);
        }
        document.body.removeChild(textarea);
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
            closeDashboardDetail();
            closeFormDetailModal();
            closeCancelConfirm();
        }
    });
</script>
</x-layouts.app>