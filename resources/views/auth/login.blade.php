<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: radial-gradient(ellipse at top, #243447 0%, #14171a 60%);
        }
        .login-card {
            background: #22262b;
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 10px 40px rgba(0, 0, 0, .5);
        }
        .brand-mark {
            width: 44px; height: 44px; border-radius: 10px;
            background: #0d6efd; color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.1rem;
        }
    </style>
</head>
<body>
<div class="container" style="max-width: 420px; padding-top: 12vh;">
    <div class="card login-card">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="brand-mark">IS</span>
                <div>
                    <h5 class="mb-0">Inventory &amp; Sales</h5>
                    <small class="text-secondary">Silakan login untuk melanjutkan.</small>
                </div>
            </div>

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf {{-- token CSRF: wajib untuk setiap form POST --}}

                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control" required>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>

                <button class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
    <p class="text-center text-secondary small mt-3">
        Demo: admin@demo.test / staff@demo.test (password: password)
    </p>
</div>
</body>
</html>
