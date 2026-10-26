<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Semua method di sini hanya Admin (route dijaga can:manage-users).
    public function index()
    {
        $users = User::orderBy('name')->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['user' => new User(['role' => 'staff'])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Cegah admin mengunci dirinya sendiri dengan menurunkan role sendiri.
        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return back()->withInput()
                ->withErrors(['role' => 'Anda tidak dapat menurunkan role akun Anda sendiri.']);
        }

        if (empty($data['password'])) {
            unset($data['password']);          // kosong = password tidak diubah
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }
}
