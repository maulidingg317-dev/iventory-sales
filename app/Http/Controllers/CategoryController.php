<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')   // hitung produk tanpa N+1
            ->when($request->filled('q'), fn ($query) =>
                $query->where('name', 'like', '%' . $request->q . '%'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();                         // pertahankan ?q= saat pindah halaman

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.form', ['category' => new Category()]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // withTrashed(): produk yang di-soft-delete tetap ada di database
        // dan tetap menahan foreign key, jadi harus ikut dihitung.
        if ($category->products()->withTrashed()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih dipakai produk.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
