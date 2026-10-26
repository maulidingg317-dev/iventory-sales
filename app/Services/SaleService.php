<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Simpan penjualan secara atomik.
     *  - harga diambil dari database (bukan dari browser)
     *  - stok diperiksa SETELAH baris produk dikunci (lockForUpdate) -> mencegah overselling
     *  - exception di dalam closure => seluruh transaksi di-rollback otomatis
     *
     * @param array<int, array{product_id:int, quantity:int}> $items
     */
    public function create(User $user, array $items): Sale
    {
        return DB::transaction(function () use ($user, $items) {
            $sale = Sale::create([
                'user_id'     => $user->id,
                'sale_number' => $this->generateNumber(),
                'total'       => 0,
                'status'      => 'completed',
                'sold_at'     => now(),
            ]);

            $total = 0;

            foreach (collect($items)->sortBy('product_id') as $item) {
                // lockForUpdate: transaksi lain yang menjual produk ini harus menunggu
                // sampai transaksi ini commit/rollback, lalu membaca stok terbaru.
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'Ada produk yang tidak ditemukan atau sudah dihapus.',
                    ]);
                }

                $qty = (int) $item['quantity'];

                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        'items' => 'Kuantitas harus lebih dari 0.',
                    ]);
                }

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak cukup (tersedia {$product->stock}, diminta {$qty}).",
                    ]);
                }

                $unitPrice = (float) $product->selling_price;   // dari database
                $subtotal  = round($qty * $unitPrice, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'unit_price' => $unitPrice,                  // snapshot harga saat transaksi
                    'subtotal'   => $subtotal,
                ]);

                $product->decrement('stock', $qty);
                $total += $subtotal;
            }

            $sale->update(['total' => round($total, 2)]);

            return $sale;
        });
    }

    /** Batalkan penjualan: stok dikembalikan, status menjadi cancelled. */
    public function cancel(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->findOrFail($sale->id);

            if ($sale->status !== 'completed') {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi ini sudah dibatalkan sebelumnya.',
                ]);
            }

            foreach ($sale->items()->orderBy('product_id')->get() as $item) {
                // withTrashed: produk yang sudah di-soft-delete tetap boleh dipulihkan stoknya.
                $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);
                $product->increment('stock', $item->quantity);
            }

            $sale->update(['status' => 'cancelled']);
        });
    }

    private function generateNumber(): string
    {
        do {
            $number = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Sale::where('sale_number', $number)->exists());

        return $number;
    }
}
