@extends('layouts.app')
@section('title', 'Pembelian Baru')

@section('content')
<h4 class="mb-3">Pembelian Baru (Stock-in)</h4>

@include('partials.errors')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('purchases.store') }}"
              onsubmit="this.querySelector('.btn-submit').disabled = true;">
            @csrf

            <div class="mb-3" style="max-width: 420px;">
                <label for="supplier_id" class="form-label">Pemasok</label>
                <select id="supplier_id" name="supplier_id" required class="form-select @error('supplier_id') is-invalid @enderror">
                    <option value="">-- pilih pemasok --</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            @include('partials.item-rows', ['products' => $products, 'mode' => 'purchase'])

            <button class="btn btn-primary btn-submit">Simpan Pembelian</button>
            <a href="{{ route('purchases.index') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
@endsection
