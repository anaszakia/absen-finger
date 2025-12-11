<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $keyword = $request->query('search');

        $users = User::with(['roles', 'permissions'])
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($q2) use ($keyword) {
                    $q2->where('name',  'like', "%{$keyword}%")
                       ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['search' => $keyword]);
            
        return view('admin.users.index', compact('users', 'keyword'));
    }

    public function create()
    {
        $this->authorize('create users');
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create users');

        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email|max:255',
            'role'      => 'required|exists:roles,name',
            'password'  => 'required|min:8|confirmed',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $role = $validated['role'];
        unset($validated['role']);
        
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->assignRole($role);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('edit users');
        $user->load('roles');
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('edit users');

        $rules = [
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email,' . $user->id . '|max:255',
            'role'      => 'required|exists:roles,name',
        ];
        
        if ($request->filled('password')) {
            $rules['password'] = 'required|min:8';
        }

        $validated = $request->validate($rules, [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $role = $validated['role'];
        unset($validated['role']);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->syncRoles([$role]);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete users');

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Tidak bisa menghapus akun Anda sendiri!');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "User {$userName} berhasil dihapus!");
    }
}
