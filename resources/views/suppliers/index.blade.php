@extends('layouts.app')
@section('title', 'Pemasok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Pemasok</h4>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">+ Tambah Pemasok</a>
</div>

<form method="GET" action="{{ route('suppliers.index') }}" class="row g-2 mb-3">
    <div class="col-sm-6 col-md-4">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, email, atau telepon...">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Cari</button>
        @if (request()->filled('q'))
            <a href="{{ route('suppliers.index') }}" class="btn btn-link">Reset</a>
        @endif
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Nama</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Pembelian</th>
                    <th style="width:160px" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($suppliers as $supplier)
                <tr>
                    <td>{{ $suppliers->firstItem() + $loop->index }}</td>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone ?? '-' }}</td>
                    <td>{{ $supplier->email ?? '-' }}</td>
                    <td>{{ $supplier->purchases_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus pemasok ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data pemasok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $suppliers->links() }}</div>
@endsection
