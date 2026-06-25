@extends('BE.layouts.main')
@section('title', 'Sensitivity Analysis')
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
                            <li class="breadcrumb-item" aria-current="page">Sensitivity Analysis</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title d-flex align-items-center justify-content-between">
                            <h2 class="mb-0">Sensitivity Analysis</h2>
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

    @if ($selectedPeriod && $sensitivityResults)
        <div class="alert alert-info mb-4">
            <strong>Cara Kerja:</strong> Analisis ini menunjukkan bagaimana perubahan bobot kriteria sebesar {{ implode(', ', array_map(fn($x) => $x . '%', $percentageChanges)) }}
            mempengaruhi ranking pelamar. Identifikasi kriteria yang paling berpengaruh terhadap hasil akhir.
        </div>

        @foreach ($sensitivityResults as $criteria_code => $sensitivity_data)
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">{{ $criteria_code }} - {{ $sensitivity_data['criteria_name'] }} (Current Weight: {{ $sensitivity_data['current_weight'] }})</h5>
                        </div>
                        <div class="card-body">
                            @if (!empty($sensitivity_data['rank_changes']))
                                <div class="row mb-3">
                                    @foreach ($sensitivity_data['rank_changes'] as $percentage => $changes)
                                        <div class="col-md-6 mb-3">
                                            <div class="alert alert-light border">
                                                <h6 class="mb-2">
                                                    <strong>{{ $percentage > 0 ? '+' : '' }}{{ $percentage }}%</strong>
                                                    <small class="text-muted">(Bobot: {{ $sensitivity_data['current_weight'] * (1 + $percentage/100) }})</small>
                                                </h6>
                                                <small>
                                                    <strong class="text-primary">{{ $changes['rank_change_count'] }} pelamar</strong> berubah posisi ranking<br>
                                                    <strong class="text-success">{{ $changes['improved_count'] }}</strong> naik posisi |
                                                    <strong class="text-danger">{{ $changes['declined_count'] }}</strong> turun posisi
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if (!empty($sensitivity_data['most_affected_applicants']))
                                    <div class="mt-3 pt-3 border-top">
                                        <h6>Pelamar Paling Terpengaruh</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Pelamar</th>
                                                        <th>Original Rank</th>
                                                        <th>Max Change</th>
                                                        <th>Worst Case ({{ min($percentageChanges) }}%)</th>
                                                        <th>Best Case ({{ max($percentageChanges) }}%)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($sensitivity_data['most_affected_applicants'] as $aff)
                                                        <tr>
                                                            <td>{{ $aff['applicant_name'] }}</td>
                                                            <td><span class="badge badge-light text-dark">{{ $aff['original_rank'] }}</span></td>
                                                            <td><strong>{{ abs($aff['max_rank_change']) }}</strong> posisi</td>
                                                            <td>{{ $aff['worst_case_rank'] }}</td>
                                                            <td>{{ $aff['best_case_rank'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-3 pt-3 border-top">
                                    <h6>Kesimpulan</h6>
                                    <p class="mb-0 small">
                                        Kriteria <strong>{{ $criteria_code }}</strong> memiliki dampak
                                        <strong class="text-primary">
                                            @if ($sensitivity_data['sensitivity_impact'] === 'high')
                                                TINGGI
                                            @elseif ($sensitivity_data['sensitivity_impact'] === 'medium')
                                                SEDANG
                                            @else
                                                RENDAH
                                            @endif
                                        </strong>
                                        terhadap perubahan ranking.
                                        {{ $sensitivity_data['conclusion'] }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    @elseif ($selectedPeriod)
        <div class="alert alert-warning">
            Tidak ada data sensitivitas untuk ditampilkan.
        </div>
    @else
        <div class="alert alert-info">
            Silakan pilih periode untuk analisis sensitivitas.
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
