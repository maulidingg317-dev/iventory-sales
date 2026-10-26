<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        // Gate = aturan "boleh/tidak". Dipakai lewat middleware can:nama-gate
        // di route (server-side), dan @can di Blade (hanya untuk tampilan).
        // Dashboard & ringkasan penjualan: fitur Admin/Owner (slide 5 brief).
        Gate::define('view-dashboard', fn (User $user) => $user->isAdmin());
        Gate::define('manage-users',  fn (User $user) => $user->isAdmin());
        Gate::define('manage-master', fn (User $user) => $user->isAdmin());
        Gate::define('manage-sales',  fn (User $user) => in_array($user->role, ['admin', 'staff'], true));

        // Admin melihat semua transaksi; Staff hanya transaksi miliknya sendiri.
        Gate::define('view-transaction', fn (User $user, $transaction) =>
            $user->isAdmin() || $transaction->user_id === $user->id);

        // Hanya Admin yang boleh membatalkan transaksi.
        Gate::define('cancel-transaction', fn (User $user) => $user->isAdmin());
    }
}
