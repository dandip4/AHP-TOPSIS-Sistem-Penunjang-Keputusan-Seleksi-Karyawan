@extends('BE.layouts.main')
@section('title', 'Dashboard')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page">Dashboard</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Dashboard</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 1: Stat Cards --}}
        <div class="row">
            <div class="col-md-6 col-xxl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-light-primary">
                                    <i class="ti ti-calendar-event f-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Total Periode</h6>
                            </div>
                        </div>
                        <div class="bg-body p-3 mt-3 rounded">
                            <div class="mt-2 row align-items-center">
                                <div class="col-7">
                                    <div id="mini-chart-period"></div>
                                </div>
                                <div class="col-5">
                                    <h3 class="mb-1">{{ $totalPeriods }}</h3>
                                    <p class="text-primary mb-0"><i class="ti ti-activity"></i> {{ $activePeriods }} aktif</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xxl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-light-success">
                                    <i class="ti ti-users f-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Total Pelamar</h6>
                            </div>
                        </div>
                        <div class="bg-body p-3 mt-3 rounded">
                            <div class="mt-2 row align-items-center">
                                <div class="col-7">
                                    <div id="mini-chart-applicant"></div>
                                </div>
                                <div class="col-5">
                                    <h3 class="mb-1">{{ $totalApplicants }}</h3>
                                    <p class="text-success mb-0"><i class="ti ti-arrow-up-right"></i> Semua</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xxl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-light-warning">
                                    <i class="ti ti-list-check f-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Kriteria Aktif</h6>
                            </div>
                        </div>
                        <div class="bg-body p-3 mt-3 rounded">
                            <div class="mt-2 row align-items-center">
                                <div class="col-7">
                                    <div id="mini-chart-criteria"></div>
                                </div>
                                <div class="col-5">
                                    <h3 class="mb-1">{{ $totalCriteria }}</h3>
                                    <p class="text-warning mb-0"><i class="ti ti-filter"></i> Penilaian</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xxl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-light-danger">
                                    <i class="ti ti-user-check f-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Hasil Seleksi</h6>
                            </div>
                        </div>
                        <div class="bg-body p-3 mt-3 rounded">
                            <div class="mt-2 row align-items-center">
                                <div class="col-7">
                                    <div id="mini-chart-result"></div>
                                </div>
                                <div class="col-5">
                                    <h3 class="mb-1">{{ $totalLulus }}</h3>
                                    <p class="text-danger mb-0"><i class="ti ti-trophy"></i> Lulus</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 2: Line Chart Tren Pelamar --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1">Tren Jumlah Pelamar per Periode</h5>
                            <p class="text-muted mb-0 f-13">Perkembangan jumlah pelamar di setiap periode seleksi</p>
                        </div>
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('periods.index') }}" class="btn btn-sm btn-link-primary">Kelola Periode</a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div id="chart-line-applicants" style="min-height: 320px;"></div>
                        @if($applicantsPerPeriod->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="ti ti-chart-line d-block f-36 mb-2 opacity-50"></i>
                            <p class="mb-0">Belum ada data periode seleksi.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 3: Ranking Chart + Lulus/Tidak Donut --}}
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Perangkingan Pelamar (Nilai Preferensi TOPSIS)</h5>
                        <a href="{{ route('calculations.results') }}" class="btn btn-sm btn-link-primary">Detail</a>
                    </div>
                    <div class="card-body">
                        <div id="chart-ranking" style="min-height: 350px;"></div>
                        @if(empty($topRankingChart))
                        <div class="text-center text-muted py-4">
                            <i class="ti ti-chart-bar-off d-block f-36 mb-2 opacity-50"></i>
                            <p class="mb-0">Belum ada data perangkingan. Lakukan perhitungan TOPSIS terlebih dahulu.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Hasil Seleksi</h5>
                    </div>
                    <div class="card-body">
                        <div id="chart-selection-result" style="min-height: 250px;"></div>
                        <div class="row g-3 mt-2">
                            <div class="col-6">
                                <div class="bg-body p-3 rounded text-center">
                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                        <span class="p-1 d-block bg-success rounded-circle me-2"><span class="visually-hidden">.</span></span>
                                        <p class="mb-0">Lulus</p>
                                    </div>
                                    <h5 class="mb-0">{{ $totalLulus }}</h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-body p-3 rounded text-center">
                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                        <span class="p-1 d-block bg-danger rounded-circle me-2"><span class="visually-hidden">.</span></span>
                                        <p class="mb-0">Tidak Lulus</p>
                                    </div>
                                    <h5 class="mb-0">{{ $totalTidakLulus }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 3: Bobot Kriteria + Rata-rata Skor --}}
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Bobot Kriteria (AHP)</h5>
                        <a href="{{ route('calculations.ahp') }}" class="btn btn-sm btn-link-primary">Detail AHP</a>
                    </div>
                    <div class="card-body">
                        <div id="chart-criteria-weights" style="min-height: 320px;"></div>
                        @if(empty($criteriaWeightsChart))
                        <div class="text-center text-muted py-4">
                            <i class="ti ti-scale-off d-block f-36 mb-2 opacity-50"></i>
                            <p class="mb-0">Belum ada bobot kriteria. Lakukan perhitungan AHP.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Rata-rata Skor per Kriteria</h5>
                    </div>
                    <div class="card-body">
                        <div id="chart-avg-score" style="min-height: 320px;"></div>
                        @if(empty($avgScorePerCriteria))
                        <div class="text-center text-muted py-4">
                            <i class="ti ti-chart-radar d-block f-36 mb-2 opacity-50"></i>
                            <p class="mb-0">Belum ada data penilaian pelamar.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 4: Gender Donut + Pendidikan Bar + Distribusi Skor --}}
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Distribusi Gender Pelamar</h5>
                    </div>
                    <div class="card-body">
                        <div id="chart-gender" style="min-height: 280px;"></div>
                        @if(empty($genderDistribution))
                        <div class="text-center text-muted py-3">Belum ada data pelamar</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Distribusi Pendidikan</h5>
                    </div>
                    <div class="card-body">
                        <div id="chart-education" style="min-height: 280px;"></div>
                        @if(empty($educationDistribution))
                        <div class="text-center text-muted py-3">Belum ada data pelamar</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Distribusi Skor Penilaian</h5>
                    </div>
                    <div class="card-body">
                        <div id="chart-score-dist" style="min-height: 280px;"></div>
                        @if(empty($scoreDistribution))
                        <div class="text-center text-muted py-3">Belum ada data penilaian</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 5: Tables --}}
        <div class="row">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Periode Seleksi Terbaru</h5>
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('periods.index') }}" class="btn btn-sm btn-link-primary">Lihat Semua</a>
                        @endif
                    </div>
                    <div class="card-body px-0 py-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Nama Periode</th>
                                        <th>Posisi</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentPeriods as $period)
                                    <tr>
                                        <td class="ps-4">
                                            <h6 class="mb-0">{{ $period->name }}</h6>
                                            <small class="text-muted">oleh {{ $period->creator->name ?? '-' }}</small>
                                        </td>
                                        <td>{{ $period->position }}</td>
                                        <td>{!! $period->status_badge !!}</td>
                                        <td class="text-end pe-4">
                                            <small class="text-muted">{{ $period->start_date->format('d M Y') }}</small>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">
                                        <i class="ti ti-calendar-off d-block f-24 mb-2 opacity-50"></i>Belum ada periode seleksi
                                    </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Top 10 Hasil Seleksi</h5>
                        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-link-primary">Laporan</a>
                    </div>
                    <div class="card-body px-0 py-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Rank</th>
                                        <th>Nama</th>
                                        <th class="text-end">Nilai</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestResults as $result)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="avtar avtar-xs {{ $result->rank <= 3 ? 'bg-light-warning' : 'bg-light-primary' }} rounded-circle d-inline-flex">
                                                <span class="f-12 fw-bold">{{ $result->rank }}</span>
                                            </div>
                                        </td>
                                        <td><h6 class="mb-0">{{ $result->applicant->name }}</h6></td>
                                        <td class="text-end"><code>{{ number_format($result->preference_value, 4) }}</code></td>
                                        <td class="text-end pe-4">{!! $result->status_badge !!}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">
                                        <i class="ti ti-trophy-off d-block f-24 mb-2 opacity-50"></i>Belum ada hasil seleksi
                                    </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        @if(Auth::user()->isAdmin())
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('periods.create') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 py-3 w-100">
                                    <i class="ti ti-calendar-plus f-20"></i> Buat Periode Baru
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('applicants.create') }}" class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2 py-3 w-100">
                                    <i class="ti ti-user-plus f-20"></i> Tambah Pelamar
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('calculations.ahp') }}" class="btn btn-outline-warning d-flex align-items-center justify-content-center gap-2 py-3 w-100">
                                    <i class="ti ti-math-function f-20"></i> Hitung AHP
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('calculations.topsis') }}" class="btn btn-outline-danger d-flex align-items-center justify-content-center gap-2 py-3 w-100">
                                    <i class="ti ti-chart-bar f-20"></i> Hitung TOPSIS
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/BE') }}/js/plugins/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var primaryColor = '#4680FF';
    var successColor = '#2ca87f';
    var warningColor = '#e58a00';
    var dangerColor  = '#dc2626';
    var infoColor    = '#3ec9d6';

    // === Mini Sparkline Charts ===
    var miniOpts = function(color, data) {
        return {
            chart: { type: 'area', height: 60, sparkline: { enabled: true } },
            colors: [color],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0, stops: [0, 100] } },
            series: [{ data: data }],
            tooltip: { fixed: { enabled: false }, x: { show: false }, marker: { show: false } }
        };
    };

    new ApexCharts(document.querySelector("#mini-chart-period"),   miniOpts(primaryColor, [0, {{ $totalPeriods }}, {{ $activePeriods }}, {{ $totalPeriods }}])).render();
    new ApexCharts(document.querySelector("#mini-chart-applicant"),miniOpts(successColor, [0, {{ intval($totalApplicants * 0.3) }}, {{ intval($totalApplicants * 0.7) }}, {{ $totalApplicants }}])).render();
    new ApexCharts(document.querySelector("#mini-chart-criteria"), miniOpts(warningColor, [{{ $totalCriteria }}, {{ $totalCriteria }}, {{ $totalCriteria }}, {{ $totalCriteria }}])).render();
    new ApexCharts(document.querySelector("#mini-chart-result"),   miniOpts(dangerColor,  [0, {{ $totalTidakLulus }}, {{ $totalLulus + $totalTidakLulus }}, {{ $totalLulus }}])).render();

    // === Chart: Line Chart Tren Pelamar ===
    @if($applicantsPerPeriod->isNotEmpty())
    new ApexCharts(document.querySelector("#chart-line-applicants"), {
        chart: { type: 'line', height: 320, toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } } },
        series: [{
            name: 'Jumlah Pelamar',
            data: @json($applicantsPerPeriod->pluck('count'))
        }],
        xaxis: {
            categories: @json($applicantsPerPeriod->pluck('name')),
            labels: { style: { fontSize: '12px' }, rotate: -25, rotateAlways: false }
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            labels: { style: { fontSize: '12px' } },
            title: { text: 'Jumlah Pelamar', style: { fontSize: '13px', fontWeight: 500 } }
        },
        colors: [primaryColor],
        stroke: { curve: 'smooth', width: 3 },
        markers: { size: 6, strokeWidth: 2, strokeColors: '#fff', hover: { size: 9 } },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 95, 100] } },
        dataLabels: { enabled: true, offsetY: -8, style: { fontSize: '12px', fontWeight: 600 }, background: { enabled: true, borderRadius: 4, padding: 4 } },
        grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
        tooltip: { y: { formatter: function(v) { return v + ' pelamar'; } } }
    }).render();
    @endif

    // === Chart: Ranking Horizontal Bar ===
    @if(!empty($topRankingChart))
    var rankingNames  = @json(array_column($topRankingChart, 'name'));
    var rankingValues = @json(array_column($topRankingChart, 'value'));
    var rankingStatus = @json(array_column($topRankingChart, 'status'));
    var rankingColors = rankingStatus.map(function(s) { return s === 'lulus' ? successColor : '#8c8c8c'; });

    new ApexCharts(document.querySelector("#chart-ranking"), {
        chart: { type: 'bar', height: 350, toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%', distributed: true } },
        colors: rankingColors,
        series: [{ name: 'Nilai Preferensi', data: rankingValues }],
        xaxis: { categories: rankingNames, labels: { style: { fontSize: '12px' } } },
        yaxis: { labels: { style: { fontSize: '12px' } } },
        dataLabels: { enabled: true, formatter: function(v) { return v.toFixed(4); }, style: { fontSize: '11px' } },
        legend: { show: false },
        tooltip: {
            y: { formatter: function(v, opts) { return v.toFixed(4) + ' (' + rankingStatus[opts.dataPointIndex] + ')'; } }
        }
    }).render();
    @endif

    // === Chart: Donut Lulus / Tidak Lulus ===
    @if($totalLulus > 0 || $totalTidakLulus > 0)
    new ApexCharts(document.querySelector("#chart-selection-result"), {
        chart: { type: 'donut', height: 250 },
        series: [{{ $totalLulus }}, {{ $totalTidakLulus }}],
        labels: ['Lulus', 'Tidak Lulus'],
        colors: [successColor, dangerColor],
        plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px' } } } } },
        legend: { position: 'bottom', fontSize: '13px' },
        dataLabels: { enabled: false }
    }).render();
    @endif

    // === Chart: Bobot Kriteria Polar Area ===
    @if(!empty($criteriaWeightsChart))
    new ApexCharts(document.querySelector("#chart-criteria-weights"), {
        chart: { type: 'polarArea', height: 320 },
        series: @json(array_column($criteriaWeightsChart, 'weight')),
        labels: @json(array_column($criteriaWeightsChart, 'code')),
        colors: [primaryColor, successColor, warningColor, dangerColor, infoColor, '#7c3aed', '#f472b6'],
        stroke: { colors: ['#fff'] },
        fill: { opacity: 0.85 },
        legend: { position: 'bottom', fontSize: '12px' },
        plotOptions: { polarArea: { rings: { strokeWidth: 1 } } },
        tooltip: {
            y: { formatter: function(v) { return v.toFixed(2) + '%'; } }
        }
    }).render();
    @endif

    // === Chart: Radar Rata-rata Skor ===
    @if(!empty($avgScorePerCriteria))
    new ApexCharts(document.querySelector("#chart-avg-score"), {
        chart: { type: 'radar', height: 320, toolbar: { show: false } },
        series: [{ name: 'Rata-rata Skor', data: @json(array_column($avgScorePerCriteria, 'avg')) }],
        xaxis: { categories: @json(array_column($avgScorePerCriteria, 'code')) },
        colors: [primaryColor],
        markers: { size: 4 },
        yaxis: { min: 0, max: 5, tickAmount: 5 },
        fill: { opacity: 0.25 },
        stroke: { width: 2 },
        tooltip: {
            y: { formatter: function(v) { return v.toFixed(2) + ' / 5.00'; } }
        }
    }).render();
    @endif

    // === Chart: Gender Donut ===
    @if(!empty($genderDistribution))
    new ApexCharts(document.querySelector("#chart-gender"), {
        chart: { type: 'donut', height: 280 },
        series: [{{ $genderDistribution['L'] ?? 0 }}, {{ $genderDistribution['P'] ?? 0 }}],
        labels: ['Laki-laki', 'Perempuan'],
        colors: [primaryColor, '#f472b6'],
        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px' } } } } },
        legend: { position: 'bottom', fontSize: '13px' },
        dataLabels: { enabled: true, formatter: function(v) { return v.toFixed(0) + '%'; } }
    }).render();
    @endif

    // === Chart: Education Bar ===
    @if(!empty($educationDistribution))
    new ApexCharts(document.querySelector("#chart-education"), {
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        series: [{ name: 'Jumlah', data: @json(array_values($educationDistribution)) }],
        xaxis: { categories: @json(array_keys($educationDistribution)) },
        colors: [warningColor],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%', distributed: true } },
        legend: { show: false },
        dataLabels: { enabled: true, style: { fontSize: '12px' } },
        tooltip: { y: { formatter: function(v) { return v + ' pelamar'; } } }
    }).render();
    @endif

    // === Chart: Score Distribution ===
    @if(!empty($scoreDistribution))
    var scoreLabels = @json(array_keys($scoreDistribution)).map(function(s) { return 'Skor ' + s; });
    var scoreValues = @json(array_values($scoreDistribution));
    var scoreColors = ['#dc2626', '#e58a00', '#3ec9d6', '#4680FF', '#2ca87f'];

    new ApexCharts(document.querySelector("#chart-score-dist"), {
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        series: [{ name: 'Frekuensi', data: scoreValues }],
        xaxis: { categories: scoreLabels },
        colors: scoreColors,
        plotOptions: { bar: { borderRadius: 6, columnWidth: '55%', distributed: true } },
        legend: { show: false },
        dataLabels: { enabled: true, style: { fontSize: '12px' } },
        tooltip: { y: { formatter: function(v) { return v + ' penilaian'; } } }
    }).render();
    @endif
});
</script>
@endpush
