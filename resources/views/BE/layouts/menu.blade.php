<div class="card pc-user-card">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <div class="avtar avtar-s bg-light-primary rounded-circle">
                    <i class="ti ti-user f-18"></i>
                </div>
            </div>
            <div class="flex-grow-1 ms-3 me-2">
                <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                <small>{{ ucfirst(Auth::user()->role) }}</small>
            </div>
            <a class="btn btn-icon btn-link-secondary avtar" data-bs-toggle="collapse" href="#pc_sidebar_userlink">
                <svg class="pc-icon">
                    <use xlink:href="#custom-sort-outline"></use>
                </svg>
            </a>
        </div>
        <div class="collapse pc-user-links" id="pc_sidebar_userlink">
            <div class="pt-3">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer; padding: 0; font: inherit;">
                        <i class="ti ti-power"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@php
    $u = Auth::user();
@endphp
<ul class="pc-navbar">
    <li class="pc-item pc-caption"><label>Menu Utama</label></li>
    <li class="pc-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <a href="{{ route('dashboard') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
            <span class="pc-mtext">Dashboard</span>
        </a>
    </li>

    @if($u->isAdmin())
    <li class="pc-item pc-caption"><label>Master Data</label></li>
    <li class="pc-item {{ request()->routeIs('periods.*') ? 'active' : '' }}">
        <a href="{{ route('periods.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-calendar-event"></i></span>
            <span class="pc-mtext">Periode Seleksi</span>
        </a>
    </li>
    <li class="pc-item {{ request()->routeIs('criteria.*') ? 'active' : '' }}">
        <a href="{{ route('criteria.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-list-check"></i></span>
            <span class="pc-mtext">Data Kriteria</span>
        </a>
    </li>
    <li class="pc-item {{ request()->routeIs('applicants.*') ? 'active' : '' }}">
        <a href="{{ route('applicants.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-users"></i></span>
            <span class="pc-mtext">Data Pelamar</span>
        </a>
    </li>
    <li class="pc-item {{ request()->routeIs('evaluators.*') ? 'active' : '' }}">
        <a href="{{ route('evaluators.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-users-group"></i></span>
            <span class="pc-mtext">Data Evaluator (KMKK)</span>
        </a>
    </li>

    <li class="pc-item pc-caption"><label>KMKK & Penilaian</label></li>
    @else
        @if($u->isEvaluatorUser())
            <li class="pc-item pc-caption"><label>Penilaian KMKK</label></li>
        @endif
    @endif

    @if($u->isAdmin() || $u->isEvaluatorUser())
    <li class="pc-item {{ request()->routeIs('evaluations.*') ? 'active' : '' }}">
        <a href="{{ route('evaluations.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-writing"></i></span>
            <span class="pc-mtext">Penilaian Pelamar</span>
        </a>
    </li>
    <li class="pc-item {{ request()->routeIs('kmkk.*') ? 'active' : '' }}">
        <a href="{{ route('kmkk.group-results') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-hierarchy"></i></span>
            <span class="pc-mtext">Evaluasi Kelompok (KMKK)</span>
        </a>
    </li>
    @endif

    @if($u->isAdmin())
    <li class="pc-item pc-caption"><label>Perhitungan</label></li>
    <li class="pc-item {{ request()->routeIs('calculations.ahp') ? 'active' : '' }}">
        <a href="{{ route('calculations.ahp') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-math-function"></i></span>
            <span class="pc-mtext">Perhitungan AHP</span>
        </a>
    </li>
    <li class="pc-item {{ request()->routeIs('calculations.topsis') ? 'active' : '' }}">
        <a href="{{ route('calculations.topsis') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-chart-bar"></i></span>
            <span class="pc-mtext">Perhitungan TOPSIS</span>
        </a>
    </li>
    <li class="pc-item {{ request()->routeIs('calculations.results') ? 'active' : '' }}">
        <a href="{{ route('calculations.results') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-trophy"></i></span>
            <span class="pc-mtext">Hasil Perangkingan</span>
        </a>
    </li>
    @endif

    @if($u->isAdmin() || $u->isDirektur() || $u->isEvaluatorUser())
    <li class="pc-item pc-caption"><label>Laporan & Info</label></li>
    <li class="pc-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <a href="{{ route('reports.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-file-text"></i></span>
            <span class="pc-mtext">Laporan Seleksi</span>
        </a>
    </li>
    @endif

    @if($u->isAdmin())
    <li class="pc-item {{ request()->routeIs('announcements.*') ? 'active' : '' }}">
        <a href="{{ route('announcements.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-speakerphone"></i></span>
            <span class="pc-mtext">Pengumuman</span>
        </a>
    </li>
    @endif
</ul>
