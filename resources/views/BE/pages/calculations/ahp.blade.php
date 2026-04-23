@extends('BE.layouts.main')
@section('title', 'Perhitungan AHP')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('calculations.ahp') }}">Perhitungan</a></li>
                            <li class="breadcrumb-item" aria-current="page">AHP</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Perhitungan AHP</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-light-primary text-primary p-2"><i class="ti ti-filter"></i></span>
                    <h5 class="mb-0">Filter periode</h5>
                </div>
                <span class="badge bg-light-primary text-primary">Periode seleksi</span>
            </div>
            <div class="card-body">
                <form method="GET" class="row align-items-end g-3">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label small text-muted mb-1">Pilih Periode Seleksi</label>
                        <select name="period_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Pilih Periode --</option>
                            @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ $periodId == $period->id ? 'selected' : '' }}>{{ $period->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if($periodId && $selectedPeriod)
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-calculator text-primary"></i>
                        <h5 class="mb-0">Hitung Bobot AHP</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('calculations.ahp.calculate') }}" class="row g-3 align-items-end">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $periodId }}">
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Jumlah kuota lulus</label>
                            <input type="number" name="pass_count" class="form-control form-control-sm" min="0" value="{{ old('pass_count', 0) }}" placeholder="0">
                        </div>
                        <div class="col-md-8 col-lg-9">
                            <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm">
                                <i class="ti ti-calculator me-2"></i>Hitung AHP (Auto)
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($ahpResult && isset($ahpResult['error']))
                <div class="alert alert-danger d-flex align-items-start gap-2 mt-3" role="alert">
                    <i class="ti ti-alert-triangle fs-4 flex-shrink-0"></i>
                    <div>{{ $ahpResult['error'] }}</div>
                </div>
            @elseif($ahpResult)
                @php
                    $ahpCriteriaIds = $ahpResult['criteria_ids'] ?? [];
                    $criteriaById = $criteria->keyBy('id');
                @endphp

                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-math-function text-primary"></i>
                            <h5 class="mb-0">Matriks Perbandingan Berpasangan</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        @foreach($ahpCriteriaIds as $colId)
                                            <th>{{ $criteriaById[$colId]->code ?? $colId }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ahpCriteriaIds as $rowId)
                                        <tr>
                                            <th class="table-light text-start">{{ $criteriaById[$rowId]->code ?? $rowId }}</th>
                                            @foreach($ahpCriteriaIds as $colId)
                                                <td>{{ number_format($ahpResult['matrix'][$rowId][$colId] ?? 0, 4) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-chart-bar text-primary"></i>
                            <h5 class="mb-0">Matriks Ternormalisasi</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        @foreach($ahpCriteriaIds as $colId)
                                            <th>{{ $criteriaById[$colId]->code ?? $colId }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ahpCriteriaIds as $rowId)
                                        <tr>
                                            <th class="table-light text-start">{{ $criteriaById[$rowId]->code ?? $rowId }}</th>
                                            @foreach($ahpCriteriaIds as $colId)
                                                <td>{{ number_format($ahpResult['normalized_matrix'][$rowId][$colId] ?? 0, 4) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-chart-pie text-primary"></i>
                            <h5 class="mb-0">Bobot Prioritas</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mb-3">
                            <table class="table table-hover table-borderless mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kriteria</th>
                                        <th class="text-end">Bobot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ahpCriteriaIds as $cid)
                                        @php $w = $ahpResult['weights'][$cid] ?? 0; @endphp
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ $criteriaById[$cid]->code ?? $cid }}</span>
                                                <span class="text-muted">— {{ $criteriaById[$cid]->name ?? '' }}</span>
                                            </td>
                                            <td class="text-end"><code class="small">{{ number_format($w, 4) }}</code></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-3"><i class="ti ti-chart-bar me-1"></i>Visualisasi bobot (proporsi relatif)</p>
                        @foreach($ahpCriteriaIds as $cid)
                            @php $w = (float) ($ahpResult['weights'][$cid] ?? 0); $pct = min(100, $w * 100); @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-medium">{{ $criteriaById[$cid]->code ?? $cid }}</span>
                                    <span class="text-muted">{{ number_format($pct, 2) }}%</span>
                                </div>
                                <div class="progress progress-primary" style="height: 0.75rem;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $criteriaById[$cid]->code ?? $cid }}: {{ number_format($pct, 2) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-checklist text-primary"></i>
                            <h5 class="mb-0">Uji Konsistensi</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <div class="bg-body p-3 rounded h-100">
                                    <small class="text-muted d-block mb-1">Lambda Max</small>
                                    <span class="fs-5 fw-semibold d-block">{{ number_format($ahpResult['lambda_max'] ?? 0, 4) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="bg-body p-3 rounded h-100">
                                    <small class="text-muted d-block mb-1">CI</small>
                                    <span class="fs-5 fw-semibold d-block">{{ number_format($ahpResult['ci'] ?? 0, 4) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="bg-body p-3 rounded h-100">
                                    <small class="text-muted d-block mb-1">RI</small>
                                    <span class="fs-5 fw-semibold d-block">{{ number_format($ahpResult['ri'] ?? 0, 4) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="bg-body p-3 rounded h-100">
                                    <small class="text-muted d-block mb-1">CR</small>
                                    <span class="fs-5 fw-semibold d-block">{{ number_format($ahpResult['cr'] ?? 0, 4) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            @if(!empty($ahpResult['is_consistent']))
                                <span class="badge bg-success fs-6 px-3 py-2"><i class="ti ti-check me-1"></i>Konsisten (CR ≤ 0,1)</span>
                            @else
                                <span class="badge bg-danger fs-6 px-3 py-2"><i class="ti ti-x me-1"></i>Tidak konsisten (CR &gt; 0,1)</span>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-start gap-2 mt-3" role="alert">
                    <i class="ti ti-info-circle fs-4 flex-shrink-0"></i>
                    <div>Belum ada matriks perbandingan untuk periode ini. Tekan <strong>Hitung AHP (Auto)</strong> untuk menghasilkan matriks dari nilai kepentingan kriteria.</div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
