@extends('layouts.app')
@section('title', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')

@section('content')
<h4 class="mb-3">{{ $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</h4>

<div class="card" style="max-width: 520px;">
    <div class="card-body">
        <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" id="name" name="name" maxlength="100" required
                       class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" maxlength="150" required
                       class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" required class="form-select @error('role') is-invalid @enderror">
                    <option value="staff" @selected(old('role', $user->role) === 'staff')>Staff</option>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                </select>
                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password @if ($user->exists)<small class="text-muted">(kosongkan jika tidak diubah)</small>@endif</label>
                <input type="password" id="password" name="password" minlength="8" autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror" @if (! $user->exists) required @endif>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="form-control">
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('users.index') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
@endsection
