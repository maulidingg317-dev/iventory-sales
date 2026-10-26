<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // Validasi di server (bukan hanya atribut HTML required).
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Auth::attempt membandingkan password dengan hash di database.
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Ganti session ID setelah login untuk mencegah session fixation.
            $request->session()->regenerate();

            // Admin -> dashboard; Staff -> daftar produk (dashboard khusus Admin).
            $home = $request->user()->isAdmin()
                ? route('dashboard')
                : route('products.index');

            return redirect()->intended($home);
        }

        // Pesan sengaja umum: tidak membocorkan apakah email terdaftar.
        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
