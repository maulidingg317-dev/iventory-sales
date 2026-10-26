@extends('layouts.app')
@section('title', 'Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Pengguna</h4>
    <a href="{{ route('users.create') }}" class="btn btn-primary">+ Tambah Pengguna</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th style="width:100px"></th></tr></thead>
            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge text-bg-{{ $user->isAdmin() ? 'primary' : 'secondary' }}">{{ $user->role }}</span></td>
                    <td class="text-end"><a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Ubah</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $users->links() }}</div>
@endsection
