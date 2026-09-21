<x-layouts.app title="Reservasi Saya">
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-white border text-slate-500 hover:bg-slate-50">&larr;</a>
        <div>
            <h1 class="text-xl font-bold text-slate-900">Reservasi Saya</h1>
            <p class="text-sm text-slate-500">Seluruh reservasi ruang rapat dan Zoom yang pernah Anda buat.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-free-border bg-free-bg px-4 py-3 text-sm text-free-text">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-busy-border bg-busy-bg px-4 py-3 text-sm text-busy-text">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white border rounded-xl overflow-hidden shadow-sm overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b text-gray-600 uppercase text-[11px]">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Jenis</th>
                    <th class="p-3">Ruangan</th>
                    <th class="p-3">Keperluan</th>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Waktu</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y text-gray-700">
                @php
                    $statusLabels = [
                        'mendatang' => ['Mendatang', 'bg-blue-100 text-blue-700'],
                        'selesai' => ['Selesai', 'bg-gray-100 text-gray-700'],
                        'menunggu_pembatalan' => ['Menunggu Pembatalan', 'bg-amber-100 text-amber-700'],
                        'dibatalkan' => ['Dibatalkan', 'bg-red-100 text-red-700'],
                    ];
                @endphp
                @forelse ($reservations as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-mono font-bold text-xs text-brand-600">{{ $r['id'] }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $r['jenis'] === 'room' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $r['jenis_label'] }}
                            </span>
                        </td>
                        <td class="p-3 font-semibold">{{ $r['ruangan'] }}</td>
                        <td class="p-3">{{ $r['keperluan'] }}</td>
                        <td class="p-3">{{ $r['tanggal'] }}</td>
                        <td class="p-3">{{ $r['waktu'] }}</td>
                        <td class="p-3">
                            @php [$label, $class] = $statusLabels[$r['status']] ?? [$r['status'], 'bg-gray-100 text-gray-700']; @endphp
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $class }}">{{ $label }}</span>
                        </td>
                        <td class="p-3">
                            @if ($r['status'] === 'mendatang')
                                <button type="button"
                                        onclick="openMineCancelConfirm('{{ $r['jenis'] }}', {{ $r['raw_id'] }}, @js($r['id']))"
                                        class="text-xs bg-red-50 text-red-600 px-2.5 py-1 rounded-md font-semibold hover:bg-red-100">
                                    Ajukan Pembatalan
                                </button>
                            @else
                                <span class="text-xs text-gray-300">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-6 text-center text-gray-400">Belum ada reservasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Konfirmasi Ajukan Pembatalan --}}
<div id="mineCancelConfirmModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h3 class="mt-4 text-lg font-bold text-slate-900">Ajukan Pembatalan?</h3>
        <p id="mineCancelConfirmText" class="mt-1.5 text-sm text-slate-500">Reservasi akan diajukan untuk dibatalkan dan menunggu persetujuan Admin.</p>
        <div class="mt-6 flex gap-3">
            <button type="button" onclick="closeMineCancelConfirm()" class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
            <button type="button" onclick="document.getElementById('mineCancelConfirmForm').submit()" class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Ya, Ajukan Pembatalan</button>
        </div>
    </div>
</div>

<form id="mineCancelConfirmForm" action="{{ route('reservations.request-cancel') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="type" id="mineCancelConfirmType">
    <input type="hidden" name="id" id="mineCancelConfirmId">
</form>

<script>
    function openMineCancelConfirm(jenis, id, label) {
        document.getElementById('mineCancelConfirmType').value = jenis;
        document.getElementById('mineCancelConfirmId').value = id;
        document.getElementById('mineCancelConfirmText').textContent =
            `Reservasi ${label ?? ''} akan diajukan untuk dibatalkan dan menunggu persetujuan Admin.`;
        const modal = document.getElementById('mineCancelConfirmModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeMineCancelConfirm() {
        const modal = document.getElementById('mineCancelConfirmModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeMineCancelConfirm();
    });
</script>
</x-layouts.app>