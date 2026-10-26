@extends('layouts.app')
@section('title', 'Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Produk @if (request()->boolean('low_stock')) <small class="text-danger fs-6">(stok rendah)</small> @endif</h4>
    @can('manage-master')
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Tambah Produk</a>
    @endcan
</div>

@include('partials.errors')

<form method="GET" action="{{ route('products.index') }}" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari SKU atau nama...">
    </div>
    <div class="col-md-3">
        <select name="category_id" class="form-select">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto d-flex align-items-center">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="low_stock" @checked(request()->boolean('low_stock'))>
            <label class="form-check-label" for="low_stock">Stok rendah saja</label>
        </div>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Filter</button>
        @if (request()->hasAny(['q', 'category_id', 'low_stock']))
            <a href="{{ route('products.index') }}" class="btn btn-link">Reset</a>
        @endif
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>SKU</th><th>Nama</th><th>Kategori</th>
                    <th class="text-end">Harga beli</th><th class="text-end">Harga jual</th>
                    <th class="text-end">Stok</th><th class="text-end">Min</th>
                    <th class="text-end" style="width:170px">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td class="text-end">{{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    <td class="text-end">
                        @if ($product->stock <= $product->minimum_stock)
                            <span class="badge text-bg-danger">{{ $product->stock }}</span>
                        @else
                            {{ $product->stock }}
                        @endif
                    </td>
                    <td class="text-end">{{ $product->minimum_stock }}</td>
                    <td class="text-end">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                        @can('manage-master')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada produk yang cocok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $products->links() }}</div>
@endsection
