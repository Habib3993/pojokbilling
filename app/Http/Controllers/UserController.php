<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location; // <-- DITAMBAHKAN
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user.
     */
    public function index()
    {
        $query = User::with('roles');

        // JIKA yang login adalah 'admin', jangan tampilkan 'superadmin'
        if (auth()->user()->hasRole('admin')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'superadmin');
            });
        }

        $users = $query->latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Menampilkan form untuk membuat user baru.
     */
    public function create()
    {
        $roles = Role::all();
        $locations = Location::all(); // <-- DITAMBAHKAN: Ambil data lokasi
        return view('users.create', compact('roles', 'locations'));
    }

    /**
     * Menyimpan user baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
            'location_id' => ['nullable', 'exists:locations,id'], // <-- DITAMBAHKAN
        ]);

        // PENCEGAHAN: Admin tidak boleh membuat superadmin
        if (auth()->user()->hasRole('admin') && $request->role === 'superadmin') {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES UNTUK MEMBUAT SUPERADMIN.');
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        // Atur location_id secara otomatis jika yang membuat adalah admin
        if (auth()->user()->hasRole('admin')) {
            $userData['location_id'] = auth()->user()->location_id;
        } else {
            $userData['location_id'] = $request->location_id;
        }
        
        $userData['role'] = $request->role; // Simpan role dari request

        $user = User::create($userData);
        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk mengedit user.
     */
    public function edit(User $user)
    {
        // PENCEGAHAN: Admin tidak boleh mengedit superadmin
        if (auth()->user()->hasRole('admin') && $user->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES UNTUK MENGEDIT SUPERADMIN.');
        }

        $roles = Role::all();
        $locations = Location::all(); // <-- DITAMBAHKAN
        $userRole = $user->roles->pluck('name')->first();
        return view('users.edit', compact('user', 'roles', 'userRole', 'locations'));
    }

    /**
     * Memperbarui data user.
     */
    public function update(Request $request, User $user)
    {
        // PENCEGAHAN: Admin tidak boleh mengedit superadmin
        if (auth()->user()->hasRole('admin') && $user->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES UNTUK MENGEDIT SUPERADMIN.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
            'location_id' => ['nullable', 'exists:locations,id'], // <-- DITAMBAHKAN
        ]);

        // PENCEGAHAN: Admin tidak boleh mengubah role menjadi superadmin
        if (auth()->user()->hasRole('admin') && $request->role === 'superadmin') {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES UNTUK MENJADIKAN USER SEBAGAI SUPERADMIN.');
        }

        $updateData = $request->only('name', 'email');
        
        // Hanya superadmin yang bisa mengubah lokasi
        if (auth()->user()->hasRole('superadmin')) {
            $updateData['location_id'] = $request->location_id;
        }
        
        $updateData['role'] = $request->role; // Simpan role dari request

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);
        $user->syncRoles($request->role);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Menghapus user.
     */
    public function destroy(User $user)
    {
        // PENCEGAHAN: Admin tidak boleh menghapus superadmin
        if (auth()->user()->hasRole('admin') && $user->hasRole('superadmin')) {
            return back()->with('error', 'Anda tidak dapat menghapus Superadmin.');
        }

        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
