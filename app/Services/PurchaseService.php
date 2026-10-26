<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    /**
     * Simpan pembelian (header + item) dan tambah stok dalam SATU database transaction.
     * Jika satu langkah gagal, semuanya di-rollback: tidak ada data setengah tersimpan.
     *
     * @param array<int, array{product_id:int, quantity:int, unit_cost:numeric}> $items
     */
    public function create(User $user, int $supplierId, array $items): Purchase
    {
        return DB::transaction(function () use ($user, $supplierId, $items) {
            $purchase = Purchase::create([
                'supplier_id'     => $supplierId,
                'user_id'         => $user->id,
                'purchase_number' => $this->generateNumber(),
                'total'           => 0,
                'status'          => 'confirmed',
                'purchased_at'    => now(),
            ]);

            $total = 0;

            // Urutkan per product_id: urutan kunci baris yang konsisten
            // mencegah deadlock saat dua transaksi memproses produk yang sama.
            foreach (collect($items)->sortBy('product_id') as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'Ada produk yang tidak ditemukan atau sudah dihapus.',
                    ]);
                }

                $qty      = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $subtotal = round($qty * $unitCost, 2);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'unit_cost'  => $unitCost,
                    'subtotal'   => $subtotal,
                ]);

                $product->increment('stock', $qty);
                $total += $subtotal;
            }

            // Total dihitung server dari item, bukan dari input browser.
            $purchase->update(['total' => round($total, 2)]);

            return $purchase;
        });
    }

    /**
     * Batalkan pembelian: stok yang tadinya bertambah dikurangi lagi.
     * Ditolak jika stok saat ini tidak cukup (barangnya sebagian sudah terjual).
     */
    public function cancel(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            // Kunci baris transaksi agar dua klik "Batalkan" tidak memulihkan stok dua kali.
            $purchase = Purchase::lockForUpdate()->findOrFail($purchase->id);

            if ($purchase->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi ini sudah dibatalkan sebelumnya.',
                ]);
            }

            foreach ($purchase->items()->orderBy('product_id')->get() as $item) {
                $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'status' => "Tidak dapat dibatalkan: stok {$product->name} saat ini {$product->stock}, "
                                  . "kurang dari {$item->quantity} yang dibeli (sebagian sudah terjual).",
                    ]);
                }

                $product->decrement('stock', $item->quantity);
            }

            $purchase->update(['status' => 'cancelled']);
        });
    }

    private function generateNumber(): string
    {
        do {
            $number = 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Purchase::where('purchase_number', $number)->exists());

        return $number;
    }
}
