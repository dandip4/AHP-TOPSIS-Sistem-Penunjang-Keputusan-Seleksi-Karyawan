<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login - SPK Seleksi Karyawan</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('assets/BE/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/BE') }}/fonts/inter/inter.css" />
    <link rel="stylesheet" href="{{ asset('assets/BE') }}/fonts/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/BE') }}/css/style.css">
    <link rel="stylesheet" href="{{ asset('assets/BE') }}/css/style-preset.css">
</head>
<body class="preset-1">
    <div class="auth-main">
        <div class="auth-wrapper v3">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="avtar avtar-l bg-light-primary rounded-circle mx-auto mb-3">
                                <i class="ti ti-chart-dots-3 f-36"></i>
                            </div>
                            <h3 class="mb-1">SPK AHP-TOPSIS</h3>
                            <p class="text-muted mb-0">Sistem Pendukung Keputusan</p>
                            <p class="text-muted">Seleksi Penerimaan Karyawan Baru</p>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="ti ti-check me-2"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="ti ti-alert-circle me-2"></i>
                                <div>
                                    @foreach($errors->all() as $error)
                                        <p class="mb-0">{{ $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="Email" value="{{ old('email') }}" required autofocus>
                                <label for="floatingEmail"><i class="ti ti-mail me-1"></i> Alamat Email</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                                <label for="floatingPassword"><i class="ti ti-lock me-1"></i> Password</label>
                            </div>
                            <div class="d-flex mt-1 justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">Ingat Saya</label>
                                </div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="ti ti-login me-2"></i>Masuk
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">
                        <div class="text-center">
                            <p class="text-muted small mb-1">Akun Demo</p>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="bg-body p-2 rounded">
                                        <small class="d-block text-muted">Admin</small>
                                        <small class="fw-bold">admin@spk.com</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-body p-2 rounded">
                                        <small class="d-block text-muted">Direktur</small>
                                        <small class="fw-bold">direktur@spk.com</small>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">Password: <code>password</code></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/BE') }}/js/plugins/bootstrap.min.js"></script>
</body>
</html>
