<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    // 'stock' sengaja TIDAK di-fillable: stok hanya boleh berubah
    // lewat logika transaksi (increment/decrement), bukan dari form.
    protected $fillable = [
        'category_id', 'sku', 'name',
        'purchase_price', 'selling_price', 'minimum_stock',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'stock'          => 'integer',
        'minimum_stock'  => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /** Produk dengan stok <= minimum (untuk dashboard & low-stock list). */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'minimum_stock');
    }

    /** Search nama/SKU, aman karena memakai parameter binding. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        }));
    }

    /** Dipakai untuk menolak hapus produk yang sudah punya transaksi. */
    public function hasTransactions(): bool
    {
        return $this->saleItems()->exists() || $this->purchaseItems()->exists();
    }
}
