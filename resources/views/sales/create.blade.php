@extends('layouts.app')
@section('title', 'Penjualan Baru')

@section('content')
<h4 class="mb-3">Penjualan Baru</h4>

@include('partials.errors')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('sales.store') }}"
              onsubmit="this.querySelector('.btn-submit').disabled = true;">
            @csrf

            @include('partials.item-rows', ['products' => $products, 'mode' => 'sale'])

            <p class="text-muted small">Harga jual diambil dari data produk di server, dan total dihitung ulang oleh server.</p>

            <button class="btn btn-primary btn-submit">Simpan Penjualan</button>
            <a href="{{ route('sales.index') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
@endsection
