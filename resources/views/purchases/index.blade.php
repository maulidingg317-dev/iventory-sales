@extends('layouts.app')
@section('title', 'Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Pembelian (Stock-in)</h4>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Pembelian Baru</a>
</div>

@include('partials.errors')

<form method="GET" action="{{ route('purchases.index') }}" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="No. transaksi">
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="">Semua status</option>
            <option value="confirmed" @selected(request('status') === 'confirmed')>Terkonfirmasi</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
        </select>
    </div>
    <div class="col-md-2"><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="Dari tanggal"></div>
    <div class="col-md-2"><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="Sampai tanggal"></div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Filter</button>
        @if (request()->hasAny(['q', 'status', 'date_from', 'date_to']))
            <a href="{{ route('purchases.index') }}" class="btn btn-link">Reset</a>
        @endif
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr><th>No. Transaksi</th><th>Tanggal</th><th>Pemasok</th><th>Petugas</th><th class="text-end">Total</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse ($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->purchase_number }}</td>
                    <td>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $purchase->supplier->name }}</td>
                    <td>{{ $purchase->user->name }}</td>
                    <td class="text-end">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                    <td>@include('partials.status-badge', ['status' => $purchase->status])</td>
                    <td class="text-end"><a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi pembelian.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $purchases->links() }}</div>
@endsection
