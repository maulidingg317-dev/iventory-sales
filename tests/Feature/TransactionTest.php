<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_increases_stock(): void
    {
        $staff    = $this->makeUser('staff');
        $product  = $this->makeProduct(5);
        $supplier = Supplier::create(['name' => 'Pemasok Uji']);

        $this->actingAs($staff)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items'       => [['product_id' => $product->id, 'quantity' => 7, 'unit_cost' => 1000]],
        ])->assertSessionHasNoErrors();

        $this->assertSame(12, $product->fresh()->stock);
        $this->assertEquals(7000, Purchase::first()->total);
    }

    public function test_sale_decreases_stock_and_total_is_calculated_by_server(): void
    {
        $staff   = $this->makeUser('staff');
        $product = $this->makeProduct(10);   // harga jual 1500

        // Browser mencoba mengirim harga dan total palsu: harus diabaikan.
        $this->actingAs($staff)->post('/sales', [
            'total' => 1,
            'items' => [['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1]],
        ])->assertSessionHasNoErrors();

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertEquals(4500, Sale::first()->total);   // 3 x 1500 dari database
    }

    public function test_sale_with_insufficient_stock_is_rejected_without_changes(): void
    {
        $product = $this->makeProduct(4);

        $this->actingAs($this->makeUser('staff'))->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 6]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, SaleItem::count());
    }

    public function test_zero_or_negative_quantity_is_rejected(): void
    {
        $product = $this->makeProduct(10);

        $this->actingAs($this->makeUser('staff'))->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 0]],
        ])->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_failure_on_one_item_rolls_back_the_whole_transaction(): void
    {
        $a = $this->makeProduct(10);   // id lebih kecil, diproses lebih dulu
        $b = $this->makeProduct(1);

        $this->actingAs($this->makeUser('staff'))->post('/sales', [
            'items' => [
                ['product_id' => $a->id, 'quantity' => 3],   // sukses & stok sempat dikurangi
                ['product_id' => $b->id, 'quantity' => 5],   // gagal: stok tidak cukup
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(10, $a->fresh()->stock, 'Stok produk A harus kembali (rollback).');
        $this->assertSame(1, $b->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, SaleItem::count());
    }

    public function test_cancelling_a_sale_restores_stock_only_once(): void
    {
        $admin   = $this->makeUser('admin');
        $product = $this->makeProduct(10);
        $sale    = app(SaleService::class)->create($admin, [['product_id' => $product->id, 'quantity' => 4]]);

        $this->assertSame(6, $product->fresh()->stock);

        $this->actingAs($admin)->post("/sales/{$sale->id}/cancel")->assertSessionHas('success');
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('cancelled', $sale->fresh()->status);

        // Pembatalan kedua tidak boleh memulihkan stok lagi.
        $this->actingAs($admin)->post("/sales/{$sale->id}/cancel")->assertSessionHas('error');
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_purchase_cannot_be_cancelled_if_its_stock_was_already_sold(): void
    {
        $admin    = $this->makeUser('admin');
        $product  = $this->makeProduct(0);
        $supplier = Supplier::create(['name' => 'Pemasok Uji']);

        $purchase = app(PurchaseService::class)->create($admin, $supplier->id, [
            ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 1000],
        ]);
        app(SaleService::class)->create($admin, [['product_id' => $product->id, 'quantity' => 3]]);

        $this->assertSame(2, $product->fresh()->stock);

        $this->actingAs($admin)->post("/purchases/{$purchase->id}/cancel")->assertSessionHas('error');

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame('confirmed', $purchase->fresh()->status);
    }
}
