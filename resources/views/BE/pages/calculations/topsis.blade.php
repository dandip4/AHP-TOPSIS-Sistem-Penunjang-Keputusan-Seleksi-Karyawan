@extends('BE.layouts.main')
@section('title', 'Perhitungan TOPSIS')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('calculations.topsis') }}">Perhitungan</a></li>
                            <li class="breadcrumb-item" aria-current="page">TOPSIS</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Perhitungan TOPSIS</h2>
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
                        <h5 class="mb-0">Hitung TOPSIS</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('calculations.topsis.calculate') }}" class="row g-3 align-items-end">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $periodId }}">
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Jumlah yang Diterima</label>
                            <input type="number" name="pass_count" class="form-control form-control-sm" min="0" value="{{ old('pass_count', 0) }}">
                            <small class="text-muted">Peringkat teratas hingga jumlah ini akan berstatus lulus.</small>
                        </div>
                        <div class="col-md-8 col-lg-9">
                            <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm">
                                <i class="ti ti-calculator me-2"></i>Hitung TOPSIS
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($topsisResult && isset($topsisResult['error']))
                <div class="alert alert-danger d-flex align-items-start gap-2 mt-3" role="alert">
                    <i class="ti ti-alert-triangle fs-4 flex-shrink-0"></i>
                    <div>{{ $topsisResult['error'] }}</div>
                </div>
            @elseif($topsisResult)
                @php
                    $criteriaIds = $topsisResult['criteria_ids'] ?? [];
                    $criteriaById = $criteria->keyBy('id');
                    $applicantsMap = $topsisResult['applicants'] ?? collect();
                    if (!$applicantsMap instanceof \Illuminate\Support\Collection) {
                        $applicantsMap = collect($applicantsMap);
                    }
                    $applicantOrder = $applicantsMap->values()->sortBy('name')->values();
                    $statusByApplicant = \App\Models\SelectionResult::where('period_id', $periodId)->get()->keyBy('applicant_id');
                    $byRank = collect($topsisResult['rankings'] ?? [])
                        ->map(fn ($rank, $applicantId) => ['applicant_id' => $applicantId, 'rank' => $rank])
                        ->sortBy('rank')
                        ->values();
                @endphp

                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-math-function text-primary"></i>
                            <h5 class="mb-0">Matriks Keputusan</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center mb-0 datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start">Nama</th>
                                        @foreach($criteriaIds as $cid)
                                            <th>{{ $criteriaById[$cid]->code ?? $cid }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applicantOrder as $applicant)
                                        <tr>
                                            <td class="text-start text-nowrap table-light">{{ $applicant->name }}</td>
                                            @foreach($criteriaIds as $cid)
                                                <td>{{ number_format($topsisResult['decision_matrix'][$applicant->id][$cid] ?? 0, 4) }}</td>
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
                            <table class="table table-bordered table-sm text-center mb-0 datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start">Nama</th>
                                        @foreach($criteriaIds as $cid)
                                            <th>{{ $criteriaById[$cid]->code ?? $cid }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applicantOrder as $applicant)
                                        <tr>
                                            <td class="text-start text-nowrap table-light">{{ $applicant->name }}</td>
                                            @foreach($criteriaIds as $cid)
                                                <td>{{ number_format($topsisResult['normalized_matrix'][$applicant->id][$cid] ?? 0, 4) }}</td>
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
                            <h5 class="mb-0">Matriks Terbobot</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center mb-0 datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start">Nama</th>
                                        @foreach($criteriaIds as $cid)
                                            <th>{{ $criteriaById[$cid]->code ?? $cid }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applicantOrder as $applicant)
                                        <tr>
                                            <td class="text-start text-nowrap table-light">{{ $applicant->name }}</td>
                                            @foreach($criteriaIds as $cid)
                                                <td>{{ number_format($topsisResult['weighted_matrix'][$applicant->id][$cid] ?? 0, 4) }}</td>
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
                            <i class="ti ti-target text-primary"></i>
                            <h5 class="mb-0">Solusi Ideal</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kriteria</th>
                                        <th class="text-end">A+</th>
                                        <th class="text-end">A−</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($criteriaIds as $cid)
                                        <tr class="table-light">
                                            <td>
                                                <span class="fw-bold">{{ $criteriaById[$cid]->code ?? $cid }}</span>
                                                <span class="text-muted">— {{ $criteriaById[$cid]->name ?? '' }}</span>
                                            </td>
                                            <td class="text-end"><code class="small">{{ number_format($topsisResult['ideal_positive'][$cid] ?? 0, 4) }}</code></td>
                                            <td class="text-end"><code class="small">{{ number_format($topsisResult['ideal_negative'][$cid] ?? 0, 4) }}</code></td>
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
                            <i class="ti ti-ruler text-primary"></i>
                            <h5 class="mb-0">Jarak &amp; Nilai Preferensi</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 table-hover datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama</th>
                                        <th class="text-end">D+</th>
                                        <th class="text-end">D−</th>
                                        <th class="text-end">Nilai Preferensi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applicantOrder as $applicant)
                                        @php
                                            $d = $topsisResult['distances'][$applicant->id] ?? ['positive' => 0, 'negative' => 0];
                                            $pref = $topsisResult['preferences'][$applicant->id] ?? 0;
                                        @endphp
                                        <tr>
                                            <td class="text-nowrap">{{ $applicant->name }}</td>
                                            <td class="text-end">{{ number_format($d['positive'], 4) }}</td>
                                            <td class="text-end">{{ number_format($d['negative'], 4) }}</td>
                                            <td class="text-end"><code class="small">{{ number_format($pref, 4) }}</code></td>
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
                            <i class="ti ti-trophy text-primary"></i>
                            <h5 class="mb-0">Hasil Perangkingan</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 table-hover datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 5rem;">Rank</th>
                                        <th>Nama</th>
                                        <th class="text-end">Nilai Preferensi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byRank as $row)
                                        @php
                                            $aid = $row['applicant_id'];
                                            $apl = $applicantsMap->get($aid);
                                            $pref = $topsisResult['preferences'][$aid] ?? 0;
                                            $saved = $statusByApplicant->get($aid);
                                            $status = $saved ? $saved->status : 'tidak_lulus';
                                            $isLulus = $status === 'lulus';
                                            $isFirst = (int) $row['rank'] === 1;
                                        @endphp
                                        <tr class="{{ $isLulus ? 'table-success' : '' }}">
                                            <td class="text-center align-middle">
                                                @if($isFirst)
                                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning text-dark fw-bold shadow-sm" style="width: 2.25rem; height: 2.25rem;" title="Peringkat 1">
                                                        <i class="ti ti-trophy"></i>
                                                    </span>
                                                @else
                                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-body text-body fw-semibold border" style="width: 2.25rem; height: 2.25rem;">{{ $row['rank'] }}</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap fw-medium">{{ $apl->name ?? '—' }}</td>
                                            <td class="text-end"><code class="small">{{ number_format($pref, 4) }}</code></td>
                                            <td>
                                                @if($isLulus)
                                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Lulus</span>
                                                @else
                                                    <span class="badge bg-danger"><i class="ti ti-x me-1"></i>Tidak lulus</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-start gap-2 mt-3" role="alert">
                    <i class="ti ti-info-circle fs-4 flex-shrink-0"></i>
                    <div>Belum ada hasil TOPSIS untuk periode ini. Pastikan AHP sudah dihitung, lalu klik <strong>Hitung TOPSIS</strong>.</div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
