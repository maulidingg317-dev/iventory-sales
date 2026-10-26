<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $cat = Category::pluck('id', 'name');

        // [sku, nama, kategori, harga beli, harga jual, stok awal, min stok]
        $rows = [
            ['MKN-001', 'Mie Instan Goreng',    'Makanan',    2800,   3500,  100, 20],
            ['MKN-002', 'Biskuit Cokelat 200g', 'Makanan',    7500,   10000,  60, 15],
            ['MNM-001', 'Air Mineral 600ml',    'Minuman',    2000,   3000,  200, 50],
            ['MNM-002', 'Teh Botol 450ml',      'Minuman',    3500,   5000,   80, 20],
            ['ATK-001', 'Pulpen Hitam',         'Alat Tulis', 1500,   2500,  120, 30],
            ['ATK-002', 'Buku Tulis 38 Lembar', 'Alat Tulis', 3000,   5000,   90, 25],
            ['KBS-001', 'Sabun Cuci Piring 800ml', 'Kebersihan', 11000, 15000, 40, 10],
            ['KBS-002', 'Pembersih Lantai 1L',  'Kebersihan', 14000,  19000,  35, 10],
            ['ELK-001', 'Baterai AA (isi 4)',   'Elektronik', 16000,  22000,  25, 10],
            // Sengaja stok rendah (<= minimum) agar fitur low-stock bisa didemokan.
            ['ELK-002', 'Kabel USB Type-C',     'Elektronik', 20000,  30000,   4, 10],
        ];

        foreach ($rows as [$sku, $name, $category, $buy, $sell, $stock, $min]) {
            $product = Product::withTrashed()->firstOrNew(['sku' => $sku]);
            $product->fill([
                'category_id'    => $cat[$category],
                'name'           => $name,
                'purchase_price' => $buy,
                'selling_price'  => $sell,
                'minimum_stock'  => $min,
            ]);
            // stock tidak fillable, set manual (hanya saat seeding).
            if (! $product->exists) {
                $product->stock = $stock;
            }
            $product->save();
        }
    }
}
