@extends('layouts.app')
@section('title', $product->exists ? 'Ubah Produk' : 'Tambah Produk')

@section('content')
<h4 class="mb-3">{{ $product->exists ? 'Ubah Produk' : 'Tambah Produk' }}</h4>

<div class="card" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
            @csrf
            @if ($product->exists) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="sku" class="form-label">SKU</label>
                    <input type="text" id="sku" name="sku" maxlength="50" required
                           class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}">
                    @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-8">
                    <label for="name" class="form-label">Nama produk</label>
                    <input type="text" id="name" name="name" maxlength="150" required
                           class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label for="category_id" class="form-label">Kategori</label>
                    <select id="category_id" name="category_id" required class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">-- pilih kategori --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="purchase_price" class="form-label">Harga beli</label>
                    <input type="number" step="0.01" min="0" id="purchase_price" name="purchase_price" required
                           class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price', $product->purchase_price) }}">
                    @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="selling_price" class="form-label">Harga jual</label>
                    <input type="number" step="0.01" min="0" id="selling_price" name="selling_price" required
                           class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price', $product->selling_price) }}">
                    @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="minimum_stock" class="form-label">Stok minimum</label>
                    <input type="number" step="1" min="0" id="minimum_stock" name="minimum_stock" required
                           class="form-control @error('minimum_stock') is-invalid @enderror" value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}">
                    @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <p class="text-muted small mt-3 mb-3">
                @if ($product->exists)
                    Stok saat ini: <strong>{{ $product->stock }}</strong>. Stok tidak diubah dari form ini, hanya lewat transaksi.
                @else
                    Produk baru berstok 0. Tambahkan stok lewat menu Pembelian.
                @endif
            </p>

            <button class="btn btn-primary">Simpan</button>
            <a href="{{ $product->exists ? route('products.show', $product) : route('products.index') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
@endsection
