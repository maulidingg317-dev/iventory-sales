@extends('layouts.app')
@section('title', $supplier->exists ? 'Ubah Pemasok' : 'Tambah Pemasok')

@section('content')
<h4 class="mb-3">{{ $supplier->exists ? 'Ubah Pemasok' : 'Tambah Pemasok' }}</h4>

<div class="card" style="max-width: 520px;">
    <div class="card-body">
        <form method="POST"
              action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
            @csrf
            @if ($supplier->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label">Nama pemasok</label>
                <input type="text" id="name" name="name" maxlength="150" required autofocus
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $supplier->name) }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Telepon</label>
                <input type="text" id="phone" name="phone" maxlength="30"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone', $supplier->phone) }}">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" maxlength="150"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $supplier->email) }}">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
@endsection
