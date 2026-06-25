@extends('BE.layouts.main')
@section('title', 'Naive Bayes Prediction')
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
                            <li class="breadcrumb-item" aria-current="page">Naive Bayes Prediction</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title d-flex align-items-center justify-content-between">
                            <h2 class="mb-0">Naive Bayes Prediction</h2>
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
        @if ($bayesModelError)
            <div class="alert alert-danger mb-4">
                {{ $bayesModelError }}
            </div>
        @endif

        @if ($bayesResults)
            <!-- Comparison with TOPSIS -->
            @if ($comparisonWithTopsis)
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Perbandingan Bayes vs TOPSIS</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="alert alert-success mb-0">
                                        <strong>Konsistensi: {{ $comparisonWithTopsis['consistency_percentage'] }}%</strong><br>
                                        <small>{{ $comparisonWithTopsis['matches'] }} dari {{ $comparisonWithTopsis['total_predictions'] }} pelamar konsisten antara Bayes dan TOPSIS</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-warning mb-0">
                                        <strong>Diskrepansi: {{ $comparisonWithTopsis['discrepancy_count'] }} pelamar</strong><br>
                                        <small>Ada perbedaan antara prediksi Bayes dan ranking TOPSIS</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Predictions Table -->
            <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Prediksi Kelulusan Pelamar</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Pelamar</th>
                                        <th>Confidence Lulus</th>
                                        <th>Confidence Tidak Lulus</th>
                                        <th>Prediksi</th>
                                        <th>Confidence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bayesResults as $pred)
                                        <tr>
                                            <td><strong>{{ $pred['applicant_name'] }}</strong></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                         style="width: {{ $pred['confidence_lulus'] }}%"
                                                         aria-valuenow="{{ $pred['confidence_lulus'] }}"
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        {{ $pred['confidence_lulus'] }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-danger" role="progressbar"
                                                         style="width: {{ $pred['confidence_tidak_lulus'] }}%"
                                                         aria-valuenow="{{ $pred['confidence_tidak_lulus'] }}"
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        {{ $pred['confidence_tidak_lulus'] }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $pred['predicted_class'] === 'lulus' ? 'badge-success' : 'badge-danger' }}">
                                                    {{ ucfirst($pred['predicted_class']) }}
                                                </span>
                                            </td>
                                            <td>{{ $pred['predicted_confidence'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discrepancies Detail -->
        @if (!empty($comparisonWithTopsis['discrepancies']) && count($comparisonWithTopsis['discrepancies']) > 0)
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Detail Diskrepansi</h5>
                        </div>
                        <div class="card-body">
                            @foreach ($comparisonWithTopsis['discrepancies'] as $disc)
                                <div class="alert alert-warning mb-2">
                                    <strong>{{ $disc['applicant_name'] }}</strong><br>
                                    <strong>Bayes:</strong> {{ $disc['bayes_prediction'] }} ({{ $disc['bayes_confidence'] }}% confidence)<br>
                                    <strong>TOPSIS:</strong> Rank #{{ $disc['topsis_rank'] }} - {{ ucfirst($disc['topsis_status']) }} (preference {{ $disc['preference_value'] }})<br>
                                    <strong>Isu:</strong> <em>{{ $disc['discrepancy_reason'] }}</em><br>
                                    <small class="text-muted">Rekomendasi: Review hasil penilaian evaluator untuk pelamar ini atau cek kesesuaian bobot kriteria</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @else
            <div class="alert alert-info">
                Tidak ada prediksi yang tersedia untuk periode ini.
            </div>
        @endif

    @elseif ($selectedPeriod)
        <div class="alert alert-danger">
            Data historis tidak cukup untuk training Naive Bayes. Butuh minimal 5 periode sebelumnya.
        </div>
    @else
        <div class="alert alert-info">
            Silakan pilih periode untuk melihat prediksi Naive Bayes.
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
