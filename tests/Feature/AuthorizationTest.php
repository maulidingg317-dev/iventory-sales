<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_open_admin_pages(): void
    {
        $this->actingAs($this->makeUser('staff'));

        foreach (['/dashboard', '/users', '/users/create', '/categories', '/suppliers', '/products/create'] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_staff_can_view_products_but_cannot_create_them(): void
    {
        $category = Category::create(['name' => 'Umum']);
        $this->actingAs($this->makeUser('staff'));

        $this->get('/products')->assertOk();

        $this->post('/products', [
            'sku' => 'X-1', 'name' => 'X', 'category_id' => $category->id,
            'purchase_price' => 1, 'selling_price' => 2, 'minimum_stock' => 0,
        ])->assertForbidden();
    }

    public function test_staff_cannot_cancel_transactions(): void
    {
        $admin   = $this->makeUser('admin');
        $product = $this->makeProduct(10);
        $sale    = app(SaleService::class)->create($admin, [['product_id' => $product->id, 'quantity' => 1]]);

        $this->actingAs($this->makeUser('staff'))
            ->post("/sales/{$sale->id}/cancel")->assertForbidden();

        $this->assertSame('completed', $sale->fresh()->status);
    }

    public function test_staff_cannot_view_another_users_transaction(): void
    {
        $admin   = $this->makeUser('admin');
        $product = $this->makeProduct(10);
        $sale    = app(SaleService::class)->create($admin, [['product_id' => $product->id, 'quantity' => 1]]);

        $this->actingAs($this->makeUser('staff'))->get("/sales/{$sale->id}")->assertForbidden();
        $this->actingAs($admin)->get("/sales/{$sale->id}")->assertOk();
    }

    public function test_admin_cannot_demote_own_account(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->put("/users/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'staff',
        ])->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->fresh()->role);
    }
}
