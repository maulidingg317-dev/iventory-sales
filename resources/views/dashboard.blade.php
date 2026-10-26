@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<h4 class="mb-4">Dashboard</h4>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-lg">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Total Produk</div>
            <div class="fs-3">{{ $stats['total_products'] }}</div>
        </div></div>
    </div>
    <div class="col-md-4 col-lg">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Stok Rendah</div>
            <div class="fs-3 text-danger">{{ $stats['low_stock'] }}</div>
        </div></div>
    </div>
    <div class="col-md-4 col-lg">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Penjualan Hari Ini</div>
            <div class="fs-5">Rp {{ number_format($stats['sales_today'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-6 col-lg">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Penjualan Bulan Ini</div>
            <div class="fs-5">Rp {{ number_format($stats['sales_this_month'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-6 col-lg">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Jumlah Transaksi</div>
            <div class="fs-3">{{ $stats['total_transactions'] }}</div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Produk stok rendah</span>
        <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="btn btn-sm btn-outline-secondary">Lihat semua</a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>SKU</th><th>Nama</th><th>Kategori</th><th>Stok</th><th>Minimum</th></tr></thead>
            <tbody>
            @forelse ($lowStockProducts as $p)
                <tr>
                    <td>{{ $p->sku }}</td>
                    <td><a href="{{ route('products.show', $p) }}">{{ $p->name }}</a></td>
                    <td>{{ $p->category->name }}</td>
                    <td class="text-danger fw-bold">{{ $p->stock }}</td>
                    <td>{{ $p->minimum_stock }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Tidak ada produk stok rendah.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
