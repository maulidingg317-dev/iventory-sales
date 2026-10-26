<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Admin & Staff (hanya lihat)
    public function index(Request $request)
    {
        $request->validate([
            'q'           => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'low_stock'   => ['nullable', 'boolean'],
        ]);

        $products = Product::with('category')              // eager loading: hindari N+1
            ->search($request->q)
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->boolean('low_stock'), fn ($q) => $q->lowStock())
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load('category')->loadCount(['saleItems', 'purchaseItems']);

        return view('products.show', compact('product'));
    }

    // Di bawah ini hanya Admin (route dijaga can:manage-master)
    public function create()
    {
        return view('products.form', [
            'product'    => new Product(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        // stock tidak ada di $fillable & tidak divalidasi: produk baru berstok 0,
        // stok hanya bertambah lewat transaksi pembelian.
        $product = Product::create($request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', 'Produk berhasil ditambahkan. Tambah stok lewat menu Pembelian.');
    }

    public function edit(Product $product)
    {
        return view('products.form', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Produk yang pernah dipakai transaksi tidak boleh dihapus (riwayat harus utuh).
        if ($product->hasTransactions()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah dipakai dalam transaksi.');
        }

        $product->delete(); // soft delete

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
