<!DOCTYPE html>
<html lang="id">

<head>
    <title>Login — SPK Seleksi Karyawan</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/BE/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/BE/fonts/inter/inter.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/BE/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/BE/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/BE/css/style.css') }}" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('assets/BE/css/style-preset.css') }}">
    <style>
        .login-card-shadow {
            box-shadow: 0 10px 40px rgba(27, 46, 94, 0.08);
            border-radius: var(--bs-border-radius-lg);
        }

        .login-demo-card {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: var(--bs-border-radius);
            backdrop-filter: blur(6px);
        }
    </style>
</head>

<body class="preset-1"
    data-pc-preset="preset-1"
    data-pc-sidebar-caption="true"
    data-pc-direction="ltr"
    data-pc-theme="light">

    <div class="auth-main">
        <div class="auth-wrapper v3">
            {{-- Panel kiri sesuai tema Able Pro .v3 — wajib ada agar grid flex tidak patah --}}
            <div class="auth-sidecontent">
                <div class="px-5 py-5 w-100">
                    <div class="mb-4">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="ti ti-chart-dots-3 text-white" style="font-size: 2rem;"></i>
                        </span>
                    </div>
                    <h2 class="text-white fw-bold mb-3">SPK AHP‑TOPSIS &amp; KMKK</h2>
                    <p class="text-white opacity-75 mb-4 pe-lg-5">
                        Sistem seleksi dengan bobot dinamis per periode, kelompok multi-evaluator,
                        serta perangkingan bermatra agregasi.
                    </p>
                    <ul class="list-unstyled text-white mb-0">
                        <li class="mb-2 d-flex align-items-start gap-2">
                            <i class="ti ti-check fw-bold mt-1"></i>
                            <span>Kriteria dapat disesuaikan per lowongan atau periode seleksi.</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start gap-2">
                            <i class="ti ti-check fw-bold mt-1"></i>
                            <span>Penilaian per evaluator kemudian diagregasi (KMKK).</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="ti ti-check fw-bold mt-1"></i>
                            <span>AHP bobot periode dan TOPSIS berbasis matriks agregasi.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="auth-form">
                <header class="auth-header d-none d-md-flex">
                    <span></span>
                </header>

                <div class="flex-grow-1 d-flex align-items-center justify-content-center w-100 py-4">
                    <div class="card login-card-shadow border shadow-sm mb-3" style="max-width: 440px;">
                        <div class="card-body px-4 py-4 px-sm-5 py-sm-5">
                            <div class="text-center mb-4">
                                <div class="avtar avtar-l bg-light-primary rounded-circle mx-auto mb-3">
                                    <i class="ti ti-lock-access f-30 text-primary"></i>
                                </div>
                                <h4 class="mb-1">Masuk ke akun Anda</h4>
                                <p class="text-muted mb-0 small">Gunakan email dan password organisasi Anda.</p>
                            </div>

                            @if (session('success'))
                                <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
                                    <i class="ti ti-check fs-5 flex-shrink-0 mt-1"></i>
                                    <div class="small">{{ session('success') }}</div>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                                    <i class="ti ti-alert-circle fs-5 flex-shrink-0 mt-1"></i>
                                    <div class="small">
                                        @foreach ($errors->all() as $error)
                                            <div @class(['mb-1' => ! $loop->last])>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" novalidate>
                                @csrf
                                <div class="mb-3">
                                    <label for="floatingEmail" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent"><i class="ti ti-mail"></i></span>
                                        <input type="email" name="email" id="floatingEmail"
                                            class="form-control @error('email') is-invalid @enderror"
                                            placeholder="nama@domain.com"
                                            value="{{ old('email') }}"
                                            autocomplete="username"
                                            required autofocus>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="floatingPassword" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent"><i class="ti ti-lock"></i></span>
                                        <input type="password" name="password" id="floatingPassword"
                                            class="form-control"
                                            placeholder="••••••••"
                                            autocomplete="current-password"
                                            required>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1"
                                            {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="remember">Ingat saya di perangkat ini</label>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                        <i class="ti ti-login me-2"></i>Masuk
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <footer class="auth-footer flex-column gap-3 align-items-center text-center pb-3">
                    <div class="w-100 px-4" style="max-width: 520px;">
                        <p class="text-muted small mb-2 fw-medium">Akun percobaan (password: <code>password</code>)</p>
                        <div class="row row-cols-1 row-cols-sm-2 g-2 small text-start">
                            <div>
                                <div class="rounded border bg-body p-2 h-100">
                                    <span class="text-muted d-block small">Administrator</span>
                                    <kbd class="small">admin@spk.com</kbd>
                                </div>
                            </div>
                            <div>
                                <div class="rounded border bg-body p-2 h-100">
                                    <span class="text-muted d-block small">Evaluator / Direktur</span>
                                    <kbd class="small mb-1 d-block">hrd@spk.com · manager@spk.com · direktur@spk.com</kbd>
                                </div>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">&copy; {{ date('Y') }} SPK Seleksi Karyawan</small>
                </footer>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/BE/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/BE/js/plugins/bootstrap.min.js') }}"></script>
</body>

</html>
