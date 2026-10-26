@extends('layouts.app')
@section('title', $sale->sale_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ $sale->sale_number }} @include('partials.status-badge', ['status' => $sale->status])</h4>
    <div>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Kembali</a>
        @can('cancel-transaction')
            @if ($sale->status === 'completed')
                <form action="{{ route('sales.cancel', $sale) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Batalkan penjualan ini? Stok akan dikembalikan.')">
                    @csrf
                    <button class="btn btn-outline-danger">Batalkan</button>
                </form>
            @endif
        @endcan
    </div>
</div>

<div class="card mb-3">
    <table class="table mb-0">
        <tr><th style="width:200px">Tanggal</th><td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td></tr>
        <tr><th>Petugas</th><td>{{ $sale->user->name }}</td></tr>
    </table>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>SKU</th><th>Produk</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot><tr><th colspan="4" class="text-end">Total</th><th class="text-end">Rp {{ number_format($sale->total, 0, ',', '.') }}</th></tr></tfoot>
        </table>
    </div>
</div>
@endsection
