<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tampilan pagination bawaan Laravel memakai Tailwind.
        // Karena layout kita memakai Bootstrap, ganti ke gaya Bootstrap 5.
        Paginator::useBootstrapFive();
    }
}
