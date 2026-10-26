<?php

namespace Tests;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    /**
     * PENGAMAN: test memakai RefreshDatabase (menghapus semua tabel).
     * Jika koneksi bukan sqlite, hentikan SEBELUM migrasi berjalan
     * agar database MySQL asli (inventory_sales) tidak terhapus.
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        if ($app['config']->get('database.default') !== 'sqlite') {
            throw new \RuntimeException(
                'Test dihentikan: koneksi database bukan sqlite. '
                . 'Aktifkan DB_CONNECTION=sqlite dan DB_DATABASE=:memory: di phpunit.xml.'
            );
        }

        return $app;
    }

    protected function makeUser(string $role = 'staff'): User
    {
        return User::create([
            'name'     => ucfirst($role) . ' Test',
            'email'    => $role . '.' . uniqid() . '@test.local',
            'password' => Hash::make('password'),
            'role'     => $role,
        ]);
    }

    protected function makeProduct(int $stock = 10, array $attrs = []): Product
    {
        static $n = 0;
        $n++;

        $category = Category::firstOrCreate(['name' => 'Umum']);

        $product = new Product(array_merge([
            'category_id'    => $category->id,
            'sku'            => 'SKU-' . $n . '-' . uniqid(),
            'name'           => 'Produk ' . $n,
            'purchase_price' => 1000,
            'selling_price'  => 1500,
            'minimum_stock'  => 2,
        ], $attrs));

        $product->stock = $stock;   // stock tidak fillable
        $product->save();

        return $product;
    }
}
