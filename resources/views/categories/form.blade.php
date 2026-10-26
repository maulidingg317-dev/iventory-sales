@extends('layouts.app')
@section('title', $category->exists ? 'Ubah Kategori' : 'Tambah Kategori')

@section('content')
<h4 class="mb-3">{{ $category->exists ? 'Ubah Kategori' : 'Tambah Kategori' }}</h4>

<div class="card" style="max-width: 520px;">
    <div class="card-body">
        <form method="POST"
              action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}">
            @csrf
            @if ($category->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label">Nama kategori</label>
                {{-- old(): isi lama dipertahankan jika validasi gagal --}}
                <input type="text" id="name" name="name" maxlength="100" required autofocus
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $category->name) }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('categories.index') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
@endsection
