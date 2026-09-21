<x-layouts.app title="Riwayat Aktivitas">
<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Riwayat Aktivitas</h1>
        <p class="text-sm text-slate-500">
            {{ $isAdmin ? 'Lihat semua riwayat aktivitas yang telah dilakukan.' : 'Lihat riwayat reservasi yang pernah Anda lakukan.' }}
        </p>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="bg-white p-4 rounded-xl border mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari ID / nama pengguna..." class="border-gray-300 rounded-lg text-sm">
        <select name="jenis" onchange="this.form.submit()" class="border-gray-300 rounded-lg text-sm">
            <option value="">Jenis Aktivitas (Semua)</option>
            <option value="room" {{ request('jenis') == 'room' ? 'selected' : '' }}>Reservasi Ruang</option>
            <option value="zoom" {{ request('jenis') == 'zoom' ? 'selected' : '' }}>Reservasi Zoom</option>
        </select>
        <select name="status" onchange="this.form.submit()" class="border-gray-300 rounded-lg text-sm">
            <option value="">Status (Semua)</option>
            <option value="mendatang" {{ request('status') == 'mendatang' ? 'selected' : '' }}>Mendatang</option>
            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
            <option value="menunggu_pembatalan" {{ request('status') == 'menunggu_pembatalan' ? 'selected' : '' }}>Menunggu Pembatalan</option>
            <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
        <input type="date" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()" placeholder="mm/dd/yyyy" class="border-gray-300 rounded-lg text-sm">
    </form>

    <!-- Tabel Aktivitas -->
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b text-gray-600 uppercase text-[11px]">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Pengguna</th>
                    <th class="p-3">Jenis Aktivitas</th>
                    <th class="p-3">Ruangan / Meet</th>
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
                @forelse ($activities as $a)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-mono font-bold text-xs text-brand-600">{{ $a['id'] }}</td>
                        <td class="p-3">{{ $a['pengguna'] }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $a['jenis'] === 'room' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $a['jenis_label'] }}
                            </span>
                        </td>
                        <td class="p-3 font-semibold">{{ $a['ruangan'] }}</td>
                        <td class="p-3">{{ $a['tanggal'] }}</td>
                        <td class="p-3">{{ $a['waktu'] }}</td>
                        <td class="p-3">
                            @php [$label, $class] = $statusLabels[$a['status']] ?? [$a['status'], 'bg-gray-100 text-gray-700']; @endphp
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $class }}">{{ $label }}</span>
                        </td>
                        <td class="p-3">
                            <button onclick='openDetailModal(@json($a))' class="text-xs bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-md font-semibold hover:bg-emerald-100">Detail</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-6 text-center text-gray-400">Belum ada aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail (read-only) -->
<div id="detailModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 border font-sans">
        <div class="flex justify-between items-center border-b pb-3 mb-3">
            <h4 class="font-bold text-gray-800" id="modalTitle">Detail Aktivitas</h4>
            <button onclick="document.getElementById('detailModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>
        <div id="modalBody" class="text-xs space-y-1.5 font-mono"></div>
    </div>
</div>

<script>
const activityStatusLabel = (status) => ({
    mendatang: 'Mendatang',
    selesai: 'Selesai',
    menunggu_pembatalan: 'Menunggu Pembatalan',
    dibatalkan: 'Dibatalkan',
}[status] || status);

function openDetailModal(a) {
    const item = a.raw_item;
    const user = a.user;

    document.getElementById('modalTitle').innerText = `Detail Aktivitas ${a.id}`;
    document.getElementById('modalBody').innerHTML = `
        <div class="text-emerald-700 font-bold mb-2">● ${activityStatusLabel(a.status)}</div>
        <p class="font-bold border-b pb-1 text-gray-900">INFORMASI PENGGUNA</p>
        <p>Nama     : ${user.name}</p>
        <p>NIP      : ${user.nip || '-'}</p>
        <p>Divisi   : ${user.divisi || '-'}</p>

        <p class="font-bold border-b pb-1 text-gray-900 pt-2">INFORMASI AKTIVITAS</p>
        <p>Jenis    : ${a.jenis_label}</p>
        <p>Ruangan  : ${a.ruangan}</p>
        <p>Tanggal  : ${a.tanggal_lengkap}</p>
        <p>Waktu    : ${a.waktu}</p>
        <p>Keperluan: ${a.keperluan}</p>
        <p>Divisi PIC: ${item.divisi_pic || '-'}</p>

        <p class="font-bold border-b pb-1 text-gray-900 pt-2">DIBUAT</p>
        <p>${new Date(item.created_at).toLocaleString()}</p>
    `;

    document.getElementById('detailModal').classList.remove('hidden');
}
</script>
</x-layouts.app>