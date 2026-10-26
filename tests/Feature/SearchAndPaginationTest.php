<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchAndPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_by_name_or_sku(): void
    {
        $this->makeProduct(5, ['name' => 'Kopi Arabika', 'sku' => 'KOP-001']);
        $this->makeProduct(5, ['name' => 'Teh Hijau',    'sku' => 'TEH-001']);

        $this->actingAs($this->makeUser('staff'));

        $this->get('/products?q=Kopi')->assertSee('Kopi Arabika')->assertDontSee('Teh Hijau');
        $this->get('/products?q=TEH-001')->assertSee('Teh Hijau')->assertDontSee('Kopi Arabika');
    }

    public function test_filter_by_category_and_low_stock(): void
    {
        $makanan = Category::create(['name' => 'Makanan']);
        $minuman = Category::create(['name' => 'Minuman']);

        $this->makeProduct(50, ['name' => 'Roti Tawar', 'category_id' => $makanan->id]);
        $this->makeProduct(1,  ['name' => 'Susu Kotak', 'category_id' => $minuman->id, 'minimum_stock' => 5]);

        $this->actingAs($this->makeUser('staff'));

        $this->get('/products?category_id=' . $makanan->id)
            ->assertSee('Roti Tawar')->assertDontSee('Susu Kotak');

        $this->get('/products?low_stock=1')
            ->assertSee('Susu Kotak')->assertDontSee('Roti Tawar');
    }

    public function test_pagination_splits_results_and_keeps_filters(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->makeProduct(5, ['name' => sprintf('Item %02d', $i)]);
        }

        $this->actingAs($this->makeUser('staff'));

        $this->get('/products')->assertSee('Item 01')->assertDontSee('Item 11');
        $this->get('/products?page=2')->assertSee('Item 11')->assertDontSee('Item 01');
        $this->get('/products?q=Item')->assertSee('q=Item', false);   // link halaman membawa ?q=
    }

    public function test_sales_date_filter(): void
    {
        $admin   = $this->makeUser('admin');
        $product = $this->makeProduct(20);

        $old    = app(SaleService::class)->create($admin, [['product_id' => $product->id, 'quantity' => 1]]);
        $recent = app(SaleService::class)->create($admin, [['product_id' => $product->id, 'quantity' => 1]]);

        Sale::whereKey($old->id)->update(['sold_at' => now()->subDays(10)]);

        $this->actingAs($admin)
            ->get('/sales?date_from=' . now()->subDays(3)->toDateString())
            ->assertSee($recent->sale_number)
            ->assertDontSee($old->sale_number);
    }
}
