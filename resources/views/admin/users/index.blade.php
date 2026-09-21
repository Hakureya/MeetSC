<x-layouts.app title="Manajemen Akun">
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Pesan Flash Notifikasi -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center justify-between shadow-sm">
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm shadow-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Statistik Card Header -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total User</span>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Aktif</span>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['aktif'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-red-600 uppercase tracking-wider">Nonaktif</span>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['nonaktif'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Admin</span>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['admin'] }}</p>
        </div>
    </div>

    <!-- Filter Bar & Tambah User -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-5">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap sm:flex-nowrap gap-2 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari ID / nama / NIP..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">
            </div>
            <select name="filter" onchange="this.form.submit()" class="border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">
                <option value="">Semua Status & Role</option>
                <option value="Aktif" {{ request('filter') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="Nonaktif" {{ request('filter') === 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                <option value="Admin" {{ request('filter') === 'Admin' ? 'selected' : '' }}>Admin</option>
                <option value="User" {{ request('filter') === 'User' ? 'selected' : '' }}>User</option>
            </select>
            @if(request('search') || request('filter'))
                <a href="{{ route('users.index') }}" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 bg-gray-100 rounded-lg flex items-center">Reset</a>
            @endif
        </form>
        <button type="button" onclick="document.getElementById('modalAddUser').classList.remove('hidden')" class="w-full sm:w-auto py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-sm shadow transition flex items-center justify-center gap-1.5">
            <span>+</span> Tambah User
        </button>
    </div>

    <!-- Tabel Daftar User -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-visible shadow-sm">
        <div class="overflow-x-auto overflow-y-visible">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="p-3.5">User</th>
                        <th class="p-3.5">NIP/NIM</th>
                        <th class="p-3.5">Divisi/Unit</th>
                        <th class="p-3.5">Role</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5">Terakhir Login</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="p-3.5">
                                <div class="font-bold text-gray-900">{{ $u->name }}</div>
                                <div class="text-xs text-gray-400">{{ $u->email }}</div>
                            </td>
                            <td class="p-3.5 font-mono text-xs">{{ $u->nip ?? '-' }}</td>
                            <td class="p-3.5">
                                <div class="font-medium text-gray-800">{{ $u->divisi ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $u->jabatan ?? '-' }}</div>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $u->role === 'admin' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $u->status === 'aktif' ? 'text-emerald-600' : 'text-red-600' }}">
                                    <span class="w-2 h-2 rounded-full {{ $u->status === 'aktif' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    {{ ucfirst($u->status) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-xs text-gray-500">
                                {{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="p-3.5 text-right">
                                <div class="relative inline-block text-left"
                                     x-data="{ open: false, top: 0, left: 0,
                                               toggle() {
                                                   this.open = !this.open;
                                                   if (this.open) {
                                                       const r = $el.getBoundingClientRect();
                                                       this.top = r.bottom + window.scrollY + 4;
                                                       this.left = r.right + window.scrollX - 208;
                                                   }
                                               } }">
                                    <button type="button" @click="toggle()" class="text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 font-bold transition">
                                        ⋮
                                    </button>
                                    <template x-teleport="body">
                                        <div x-show="open"
                                             x-cloak
                                             @click.outside="open = false"
                                             :style="`top: ${top}px; left: ${left}px;`"
                                             class="fixed w-52 bg-white border border-gray-100 rounded-xl shadow-xl z-50 py-1.5 text-xs text-left divide-y divide-gray-100">
                                            <div class="py-1">
                                                <button type="button" onclick='showUserDetail(@json($u))' class="w-full text-left px-4 py-2 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                                    Lihat Detail
                                                </button>
                                                <button type="button" onclick='openEditUserModal(@json($u))' class="w-full text-left px-4 py-2 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                                    Edit User
                                                </button>
                                            </div>
                                            <div class="py-1">
                                                @unless ($u->id === auth()->id())
                                                    <form action="{{ route('users.toggle', $u->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 text-gray-700 transition">
                                                            {{ $u->status === 'aktif' ? 'Nonaktifkan User' : 'Aktifkan User' }}
                                                        </button>
                                                    </form>
                                                @endunless
                                                <button type="button" onclick='openResetPasswordModal(@json($u))' class="w-full text-left px-4 py-2 hover:bg-gray-50 text-amber-600 transition">
                                                    Reset Password
                                                </button>
                                            </div>
                                            <div class="py-1">
                                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini selamanya?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 transition">
                                                        Hapus User
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400 text-sm">
                                Tidak ada data user yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($users, 'hasPages') && $users->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal 1: Tambah User Baru -->
<div id="modalAddUser" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="font-bold text-gray-800 text-base">Tambah User Baru</h3>
            <button type="button" onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
        </div>
        <form action="{{ route('users.store') }}" method="POST" class="space-y-3.5 text-sm">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Nama user" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Email</label>
                <input type="email" name="email" required placeholder="user@perusahaan.co.id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">NIP / NIM</label>
                    <input type="text" name="nip" required placeholder="198xxxxx" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Role</label>
                    <select name="role" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Divisi / Unit</label>
                    <input type="text" name="divisi" required placeholder="Contoh: PDSI" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Jabatan</label>
                    <input type="text" name="jabatan" required placeholder="Contoh: Staff" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Password Awal</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="pt-3 flex gap-2">
                <button type="button" onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="w-1/2 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-medium">Batal</button>
                <button type="submit" class="w-1/2 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow transition">Simpan User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Detail Akun Pengguna -->
<div id="modalDetailUser" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="font-bold text-gray-800 text-base">Detail Akun Pengguna</h3>
            <button type="button" onclick="document.getElementById('modalDetailUser').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
        </div>
        <div id="detailUserContent" class="space-y-2.5 text-sm text-gray-700"></div>
        <div class="mt-6 pt-3 border-t">
            <button type="button" onclick="document.getElementById('modalDetailUser').classList.add('hidden')" class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal 3: Edit User -->
<div id="modalEditUser" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="font-bold text-gray-800 text-base">Edit Akun Pengguna</h3>
            <button type="button" onclick="document.getElementById('modalEditUser').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
        </div>
        <form id="formEditUser" method="POST" class="space-y-3.5 text-sm">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Email</label>
                <input type="email" name="email" id="edit_email" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">NIP / NIM</label>
                    <input type="text" name="nip" id="edit_nip" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Role</label>
                    <select name="role" id="edit_role" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Divisi / Unit</label>
                    <input type="text" name="divisi" id="edit_divisi" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Jabatan</label>
                    <input type="text" name="jabatan" id="edit_jabatan" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
            <div class="pt-3 flex gap-2">
                <button type="button" onclick="document.getElementById('modalEditUser').classList.add('hidden')" class="w-1/2 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-medium">Batal</button>
                <button type="submit" class="w-1/2 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalResetPassword" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="font-bold text-gray-800 text-base">Reset Password</h3>
            <button type="button" onclick="document.getElementById('modalResetPassword').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
        </div>
        <p class="mb-4 text-sm text-gray-500">
            Masukkan password baru untuk <span id="reset_password_user_name" class="font-semibold text-gray-800"></span>.
        </p>
        <form id="formResetPassword" method="POST" class="space-y-3.5 text-sm">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Password Baru</label>
                <input type="password" name="password" id="reset_password" required minlength="6"
                       placeholder="Minimal 6 karakter"
                       class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="reset_password_confirmation" required minlength="6"
                       placeholder="Ulangi password baru"
                       class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500">
            </div>
            <p id="reset_password_error" class="hidden text-xs font-semibold text-red-600"></p>
            <div class="pt-3 flex gap-2">
                <button type="button" onclick="document.getElementById('modalResetPassword').classList.add('hidden')" class="w-1/2 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-medium">Batal</button>
                <button type="submit" class="w-1/2 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow transition">Simpan Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function showUserDetail(user) {
    const createdAt = user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric'
    }) : '-';

    document.getElementById('detailUserContent').innerHTML = `
        <div class="flex items-center gap-2 mb-3 pb-2 border-b">
            <span class="w-2.5 h-2.5 rounded-full ${user.status === 'aktif' ? 'bg-emerald-500' : 'bg-red-500'}"></span>
            <span class="font-bold ${user.status === 'aktif' ? 'text-emerald-700' : 'text-red-700'}">${user.status === 'aktif' ? 'Aktif' : 'Nonaktif'}</span>
        </div>
        <p><span class="w-36 inline-block text-gray-500">Nama</span>: <span class="font-semibold text-gray-900">${user.name}</span></p>
        <p><span class="w-36 inline-block text-gray-500">Email</span>: ${user.email}</p>
        <p><span class="w-36 inline-block text-gray-500">NIP</span>: <span class="font-mono">${user.nip || '-'}</span></p>
        <p><span class="w-36 inline-block text-gray-500">Divisi</span>: ${user.divisi || '-'}</p>
        <p><span class="w-36 inline-block text-gray-500">Jabatan</span>: ${user.jabatan || '-'}</p>
        <p><span class="w-36 inline-block text-gray-500">Role</span>: <span class="uppercase font-semibold text-blue-600">${user.role}</span></p>
        <p><span class="w-36 inline-block text-gray-500">Status</span>: ${user.status}</p>
        <p><span class="w-36 inline-block text-gray-500">Kapan Akun dibuat</span>: ${createdAt}</p>
        <p><span class="w-36 inline-block text-gray-500">Terakhir Login</span>: ${user.last_login_at || '-'}</p>
    `;
    document.getElementById('modalDetailUser').classList.remove('hidden');
}

function openEditUserModal(user) {
    const form = document.getElementById('formEditUser');
    form.action = `/manajemen-user/${user.id}`;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_nip').value = user.nip || '';
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_divisi').value = user.divisi || '';
    document.getElementById('edit_jabatan').value = user.jabatan || '';
    document.getElementById('modalEditUser').classList.remove('hidden');
}

function openResetPasswordModal(user) {
    const form = document.getElementById('formResetPassword');
    form.action = `/manajemen-user/${user.id}/reset-password`;
    document.getElementById('reset_password_user_name').textContent = user.name;
    document.getElementById('reset_password').value = '';
    document.getElementById('reset_password_confirmation').value = '';
    document.getElementById('reset_password_error').classList.add('hidden');
    document.getElementById('modalResetPassword').classList.remove('hidden');
}

document.getElementById('formResetPassword').addEventListener('submit', function (e) {
    const password = document.getElementById('reset_password').value;
    const confirmation = document.getElementById('reset_password_confirmation').value;
    const errorEl = document.getElementById('reset_password_error');

    if (password !== confirmation) {
        e.preventDefault();
        errorEl.textContent = 'Konfirmasi password tidak cocok dengan password baru.';
        errorEl.classList.remove('hidden');
    }
});
</script>
</x-layouts.app>