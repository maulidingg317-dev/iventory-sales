<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $override = []): array
    {
        $category = Category::firstOrCreate(['name' => 'Umum']);

        return array_merge([
            'sku'            => 'NEW-001',
            'name'           => 'Produk Baru',
            'category_id'    => $category->id,
            'purchase_price' => 1000,
            'selling_price'  => 1500,
            'minimum_stock'  => 5,
        ], $override);
    }

    public function test_admin_can_create_product(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/products', $this->validPayload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['sku' => 'NEW-001', 'stock' => 0]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $this->makeProduct(5, ['sku' => 'DUP-1']);

        $this->actingAs($this->makeUser('admin'))
            ->post('/products', $this->validPayload(['sku' => 'dup-1']))   // huruf kecil pun dianggap sama
            ->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::where('sku', 'DUP-1')->count());
    }

    public function test_negative_price_is_rejected(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/products', $this->validPayload(['selling_price' => -5]))
            ->assertSessionHasErrors('selling_price');

        $this->assertDatabaseMissing('products', ['sku' => 'NEW-001']);
    }

    public function test_product_used_in_a_transaction_cannot_be_deleted(): void
    {
        $admin   = $this->makeUser('admin');
        $product = $this->makeProduct(10);

        app(SaleService::class)->create($admin, [['product_id' => $product->id, 'quantity' => 1]]);

        $this->actingAs($admin)->delete('/products/' . $product->id)
            ->assertSessionHas('error');

        $this->assertNull($product->fresh()->deleted_at);
    }

    public function test_unused_product_is_soft_deleted(): void
    {
        $product = $this->makeProduct(0);

        $this->actingAs($this->makeUser('admin'))->delete('/products/' . $product->id);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
