<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        if ($divisi = $request->get('divisi')) {
            $query->where('divisi', $divisi);
        }

        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%")
                ->orWhere('divisi', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => User::count(),
            'aktif' => User::where('status', 'aktif')->count(),
            'nonaktif' => User::where('status', 'nonaktif')->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        $divisiOptions = User::whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->distinct()
            ->orderBy('divisi')
            ->pluck('divisi');

        return view('admin.users.index', compact('users', 'stats', 'divisiOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nip' => 'required|string|unique:users,nip',
            'divisi' => 'required|string',
            'jabatan' => 'required|string',
            'role' => 'required|in:admin,user',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nip' => $validated['nip'],
            'divisi' => $validated['divisi'],
            'jabatan' => $validated['jabatan'],
            'role' => $validated['role'],
            'status' => 'aktif',
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'nip' => 'required|string|unique:users,nip,'.$user->id,
            'divisi' => 'required|string',
            'jabatan' => 'required|string',
            'role' => 'required|in:admin,user',
        ]);

        $user->update($validated);

        return back()->with('success', 'Data user berhasil diperbarui.');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json($user);
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        // Admin tidak boleh mengubah status akunnya sendiri (tombolnya juga
        // disembunyikan di tampilan, ini adalah pengaman tambahan di server).
        if ((int) $id === (int) auth()->id()) {
            return back()->withErrors([
                'status' => 'Anda tidak dapat mengubah status akun Anda sendiri.',
            ]);
        }

        $user->status = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->save();

        return back()->with('success', 'Status user berhasil diperbarui.');
    }

    public function resetPassword(Request $request, $id)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password untuk '.$user->name.' berhasil diperbarui.');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}