<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventory & Sales')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1d21; }
        .card { background: #22262b; border-color: rgba(255,255,255,.08); }
        .table { --bs-table-bg: transparent; }
        .card-header { background: rgba(255,255,255,.03); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg border-bottom border-secondary" style="background:#14171a;" data-bs-theme="dark">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">Inventory &amp; Sales</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                @can('view-dashboard')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                @endcan
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('products.*') && ! request()->boolean('low_stock') ? 'active' : '' }}" href="{{ route('products.index') }}">Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('products.index') && request()->boolean('low_stock') ? 'active' : '' }}" href="{{ route('products.index', ['low_stock' => 1]) }}">Stok Rendah</a>
                </li>
                @can('manage-sales')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">Pembelian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">Penjualan</a>
                    </li>
                @endcan
                {{-- @can hanya menyembunyikan menu. Perlindungan sebenarnya ada di route (can:...). --}}
                @can('manage-master')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">Pemasok</a>
                    </li>
                @endcan
                @can('manage-users')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Pengguna</a>
                    </li>
                @endcan
            </ul>
            <span class="navbar-text me-3">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-outline-light btn-sm">Logout</button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
