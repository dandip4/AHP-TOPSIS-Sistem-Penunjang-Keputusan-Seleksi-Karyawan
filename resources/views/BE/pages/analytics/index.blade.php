@extends('BE.layouts.main')
@section('title', 'Analytics Dashboard')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page">Analytics Dashboard</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title d-flex align-items-center justify-content-between">
                            <h2 class="mb-0">Analytics Dashboard</h2>
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

    @if ($selectedPeriod)
        <!-- Score Distribution Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Distribusi Skor per Kriteria</h5>
                    </div>
                    <div class="card-body">
                        @if (!empty($scoreDistribution['criteria_stats']))
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Kriteria</th>
                                            <th>Min</th>
                                            <th>Max</th>
                                            <th>Mean</th>
                                            <th>Median</th>
                                            <th>StDev</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($scoreDistribution['criteria_stats'] as $stat)
                                            <tr>
                                                <td><strong>{{ $stat['criteria_code'] }}</strong> - {{ $stat['criteria_name'] }}</td>
                                                <td>{{ $stat['min'] }}</td>
                                                <td>{{ $stat['max'] }}</td>
                                                <td><span class="badge badge-light text-dark">{{ $stat['mean'] }}</span></td>
                                                <td>{{ $stat['median'] }}</td>
                                                <td>{{ $stat['stdev'] }}</td>
                                                <td>{{ $stat['count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Outliers Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Outliers (Skor Ekstrim)</h5>
                    </div>
                    <div class="card-body">
                        @if (count($outliers) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Pelamar</th>
                                            <th>Kriteria</th>
                                            <th>Skor</th>
                                            <th>Range Normal</th>
                                            <th>Tipe</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($outliers as $outlier)
                                            <tr class="{{ $outlier['outlier_type'] === 'high' ? 'table-success' : 'table-danger' }}">
                                                <td><strong>{{ $outlier['applicant_name'] }}</strong></td>
                                                <td>{{ $outlier['criteria_code'] }} - {{ $outlier['criteria_name'] }}</td>
                                                <td><strong>{{ $outlier['score'] }}</strong></td>
                                                <td>{{ $outlier['lower_bound'] }} - {{ $outlier['upper_bound'] }}</td>
                                                <td>
                                                    <span class="badge badge-light text-dark">
                                                        {{ ucfirst($outlier['outlier_type']) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">Tidak ada outlier terdeteksi.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluator Performance -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Performa Evaluator</h5>
                    </div>
                    <div class="card-body">
                        @if (!empty($evaluatorStats['evaluator_stats']))
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Evaluator</th>
                                            <th>Rata-rata Skor</th>
                                            <th>StDev</th>
                                            <th>Range</th>
                                            <th>Jumlah Evaluasi</th>
                                            <th>Stringency</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($evaluatorStats['evaluator_stats'] as $eval)
                                            <tr>
                                                <td><strong>{{ $eval['evaluator_name'] }}</strong></td>
                                                <td>
                                                    <span class="badge badge-light text-dark">{{ $eval['avg_score'] }}</span>
                                                </td>
                                                <td>{{ $eval['stdev'] }}</td>
                                                <td>{{ $eval['min_score'] }} - {{ $eval['max_score'] }}</td>
                                                <td>{{ $eval['evaluation_count'] }}</td>
                                                <td>
                                                    <span class="badge badge-light text-dark">
                                                        {{ ucfirst(str_replace('_', ' ', $eval['stringency'])) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Discriminative Criteria -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Kriteria Paling Diskriminatif</h5>
                    </div>
                    <div class="card-body">
                        @if (!empty($discriminativeCriteria['discriminative_criteria']))
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Kriteria</th>
                                            <th>Bobot</th>
                                            <th>Variance</th>
                                            <th>Correlation</th>
                                            <th>Discriminative Index</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($discriminativeCriteria['discriminative_criteria'] as $crit)
                                            <tr>
                                                <td><strong>{{ $crit['criteria_code'] }}</strong> - {{ $crit['criteria_name'] }}</td>
                                                <td><span class="badge badge-light text-dark">{{ $crit['weight'] }}</span></td>
                                                <td>{{ $crit['variance'] }}</td>
                                                <td>{{ $crit['correlation_score'] }}</td>
                                                <td>
                                                    <strong class="text-primary">{{ $crit['discriminative_index'] }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quality Control Flags -->
        @if (count($qualityFlags) > 0)
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Quality Control Flags</h5>
                        </div>
                        <div class="card-body">
                            @foreach ($qualityFlags as $flag)
                                <div class="alert alert-{{ $flag['severity'] === 'high' ? 'danger' : 'warning' }} mb-2">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $flag['flag_type'])) }}</strong><br>
                                    {{ $flag['issue'] }}<br>
                                    <small class="text-muted">{{ $flag['recommendation'] }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Navigation Links -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <a href="{{ route('analytics.trend') }}" class="btn btn-sm btn-outline-primary">Trend Analysis</a>
                        <a href="{{ route('analytics.prediction', ['period_id' => $periodId]) }}" class="btn btn-sm btn-outline-info">Naive Bayes Prediction</a>
                        <a href="{{ route('analytics.sensitivity', ['period_id' => $periodId]) }}" class="btn btn-sm btn-outline-warning">Sensitivity Analysis</a>
                        <a href="{{ route('analytics.quality-control', ['period_id' => $periodId]) }}" class="btn btn-sm btn-outline-danger">Quality Control</a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Silakan pilih periode untuk melihat analytics detail.
        </div>
    @endif
    </div>
</div>
@endsection
