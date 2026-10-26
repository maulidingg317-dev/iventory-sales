@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ $product->name }}</h4>
    <div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Kembali</a>
        @can('manage-master')
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">Ubah</a>
            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Hapus produk ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger">Hapus</button>
            </form>
        @endcan
    </div>
</div>

<div class="card" style="max-width: 640px;">
    <table class="table mb-0">
        <tr><th style="width:200px">SKU</th><td>{{ $product->sku }}</td></tr>
        <tr><th>Kategori</th><td>{{ $product->category->name }}</td></tr>
        <tr><th>Harga beli</th><td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td></tr>
        <tr><th>Harga jual</th><td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td></tr>
        <tr>
            <th>Stok</th>
            <td>
                {{ $product->stock }}
                @if ($product->stock <= $product->minimum_stock)
                    <span class="badge text-bg-danger ms-1">Stok rendah</span>
                @endif
            </td>
        </tr>
        <tr><th>Stok minimum</th><td>{{ $product->minimum_stock }}</td></tr>
        <tr><th>Dipakai di pembelian</th><td>{{ $product->purchase_items_count }} baris</td></tr>
        <tr><th>Dipakai di penjualan</th><td>{{ $product->sale_items_count }} baris</td></tr>
    </table>
</div>
@endsection
