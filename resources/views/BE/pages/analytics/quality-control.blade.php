@extends('BE.layouts.main')
@section('title', 'Quality Control Report')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('analytics.index') }}">Analytics</a></li>
                            <li class="breadcrumb-item" aria-current="page">Quality Control Report</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title d-flex align-items-center justify-content-between">
                            <h2 class="mb-0">Quality Control Report</h2>
                            <form method="GET" class="form-inline">
                                <select name="period_id" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="">Pilih Periode...</option>
                                    @foreach ($periods as $p)
                                        <option value="{{ $p->id }}" {{ $periodId == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @if ($selectedPeriod && $qualityReport && count($qualityReport) > 0)
        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0 text-danger">
                            @php
                                $highSeverity = array_filter($qualityReport, fn($q) => $q['severity'] === 'high');
                            @endphp
                            {{ count($highSeverity) }}
                        </h3>
                        <small class="text-muted">Critical Issues</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0 text-warning">
                            @php
                                $mediumSeverity = array_filter($qualityReport, fn($q) => $q['severity'] === 'medium');
                            @endphp
                            {{ count($mediumSeverity) }}
                        </h3>
                        <small class="text-muted">Medium Issues</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0 text-info">
                            @php
                                $lowSeverity = array_filter($qualityReport, fn($q) => $q['severity'] === 'low');
                            @endphp
                            {{ count($lowSeverity) }}
                        </h3>
                        <small class="text-muted">Low Issues</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0 text-success">{{ count($qualityReport) }}</h3>
                        <small class="text-muted">Total Flags</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quality Flags by Type -->
        @php
            $groupedByType = [];
            foreach ($qualityReport as $flag) {
                $type = $flag['flag_type'];
                if (!isset($groupedByType[$type])) {
                    $groupedByType[$type] = [];
                }
                $groupedByType[$type][] = $flag;
            }
        @endphp

        @foreach ($groupedByType as $flagType => $flags)
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                @if ($flagType === 'evaluator_variance')
                                    Evaluator Variance Issues
                                @elseif ($flagType === 'outlier_scores')
                                    Outlier Score Issues
                                @elseif ($flagType === 'pass_rate_anomaly')
                                    Pass Rate Anomaly
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $flagType)) }}
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach ($flags as $flag)
                                <div class="alert alert-{{ $flag['severity'] === 'high' ? 'danger' : ($flag['severity'] === 'medium' ? 'warning' : 'info') }} mb-2">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <strong>{{ $flag['issue'] }}</strong><br>
                                            <small class="text-muted">{{ $flag['details'] ?? '' }}</small>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <span class="badge badge-{{ $flag['severity'] === 'high' ? 'danger' : ($flag['severity'] === 'medium' ? 'warning' : 'info') }}">
                                                {{ ucfirst($flag['severity']) }} Severity
                                            </span>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="small">
                                        <strong>Rekomendasi:</strong> {{ $flag['recommendation'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    @elseif ($selectedPeriod)
        <div class="alert alert-success">
            Tidak ada quality control flags. Sistem dalam kondisi baik.
        </div>
    @else
        <div class="alert alert-info">
            Silakan pilih periode untuk melihat quality control report.
        </div>
    @endif

    <!-- Navigation -->
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Analytics</a>
        </div>
    </div>
    </div>
</div>
@endsection
