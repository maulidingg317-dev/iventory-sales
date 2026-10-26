<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Pintu masuk menurut role: Admin -> dashboard, Staff -> produk.
// Tamu otomatis diarahkan ke /login oleh middleware auth pada tujuan akhirnya.
Route::get('/', function () {
    return redirect()->route(
        auth()->check() && auth()->user()->isAdmin() ? 'dashboard' : 'products.index'
    );
});

// Hanya tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

// Semua yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard & ringkasan penjualan: hanya Admin/Owner.
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:view-dashboard')
        ->name('dashboard');

    // ---- Hanya Admin ----
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:manage-master')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('suppliers', SupplierController::class)->except('show');
        // create/store/edit/update/destroy produk: Admin saja
        Route::resource('products', ProductController::class)->except(['index', 'show']);
    });

    // Produk: Staff boleh melihat. Didaftarkan SETELAH /products/create
    // supaya kata "create" tidak ditangkap sebagai {product}.
    Route::resource('products', ProductController::class)->only(['index', 'show']);

    // ---- Transaksi: Admin & Staff ----
    Route::middleware('can:manage-sales')->group(function () {
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    });

    // ---- Pembatalan transaksi: Admin saja ----
    Route::middleware('can:cancel-transaction')->group(function () {
        Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    });
});
