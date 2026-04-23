<!DOCTYPE html>
<html lang="id">

<head>
    <title>SPK Seleksi Karyawan - @yield('title', 'Dashboard')</title>
    <meta charset="utf-8">
    <link rel="icon" href="{{ asset('assets/BE/images/favicon.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sistem Pendukung Keputusan Seleksi Penerimaan Karyawan Baru - AHP & TOPSIS">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('BE.layouts.css')
</head>

<body class="layout-2" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast="" data-pc-theme="light">

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <nav class="pc-sidebar">
        <div class="navbar-wrapper">
            <div class="m-header">
                <a href="{{ route('dashboard') }}" class="b-brand text-primary">
                    <h5 class="m-0 text-white"><i class="ti ti-chart-dots-3 me-2"></i>SPK AHP-TOPSIS</h5>
                </a>
            </div>
            <div class="navbar-content">
                @include('BE.layouts.menu')
            </div>
        </div>
    </nav>

    <header class="pc-header">
        <div class="header-wrapper">
            <div class="me-auto pc-mob-drp">
                <ul class="list-unstyled">
                    <li class="pc-h-item pc-sidebar-collapse">
                        <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                    <li class="pc-h-item pc-sidebar-popup">
                        <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                </ul>
            </div>
            @include('BE.layouts.header')
        </div>
    </header>

    @yield('container')

    @include('BE.layouts.footer')
    @include('BE.layouts.script')
</body>

</html>
