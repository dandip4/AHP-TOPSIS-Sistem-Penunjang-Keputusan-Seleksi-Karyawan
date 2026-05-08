@extends('BE.layouts.main')
@section('title', 'Laporan Seleksi')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page">Laporan Seleksi</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0"><i class="ti ti-file-text me-2 text-primary"></i>Laporan Seleksi</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="mb-0"><i class="ti ti-filter me-2"></i>Filter periode</h5>
                <span class="text-muted small">Pilih periode untuk menampilkan data</span>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small text-muted mb-1">Periode seleksi</label>
                        <select name="period_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">— Pilih periode —</option>
                            @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ $periodId == $period->id ? 'selected' : '' }}>{{ $period->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedPeriod && $results->isNotEmpty())
        <style>
            .report-matriks-panel .table thead th {
                font-size: .72rem;
                text-transform: uppercase;
                letter-spacing: .04em;
                color: var(--bs-secondary-color);
                white-space: nowrap;
                vertical-align: bottom;
            }
            .report-matriks-num {
                font-variant-numeric: tabular-nums;
            }
            .report-matriks-panel tbody td {
                vertical-align: middle;
            }
            .report-matriks-panel .report-inline-filter-row th {
                font-weight: 400;
                vertical-align: middle;
            }
        </style>
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="mb-0"><i class="ti ti-info-circle me-2"></i>Ringkasan periode</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-body p-3 rounded h-100 border border-secondary border-opacity-10">
                            <small class="text-muted d-block mb-1">Nama periode</small>
                            <strong class="d-block text-truncate" title="{{ $selectedPeriod->name }}">{{ $selectedPeriod->name }}</strong>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-body p-3 rounded h-100 border border-secondary border-opacity-10">
                            <small class="text-muted d-block mb-1">Posisi</small>
                            <strong class="d-block">{{ $selectedPeriod->position }}</strong>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-body p-3 rounded h-100 border border-secondary border-opacity-10">
                            <small class="text-muted d-block mb-1">Rentang tanggal</small>
                            <strong class="d-block">{{ $selectedPeriod->start_date?->format('d M Y') }} — {{ $selectedPeriod->end_date?->format('d M Y') }}</strong>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-body p-3 rounded h-100 border border-secondary border-opacity-10">
                            <small class="text-muted d-block mb-1">Dibuat oleh</small>
                            <strong class="d-block">{{ $selectedPeriod->creator->name ?? '—' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="mb-0"><i class="ti ti-scale me-2"></i>Bobot kriteria</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-sm mb-0 align-middle">
                        <thead class="border-bottom">
                            <tr>
                                <th class="ps-4">Kode</th>
                                <th>Nama kriteria</th>
                                <th class="text-end pe-4">Bobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($criteria as $criterion)
                            @php $cw = $weights->get($criterion->id); @endphp
                            <tr>
                                <td class="ps-4"><span class="badge bg-light-primary">{{ $criterion->code }}</span></td>
                                <td>{{ $criterion->name }}</td>
                                <td class="text-end pe-4 font-monospace small">{{ $cw ? number_format((float) $cw->weight, 6) : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada kriteria aktif</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3 report-matriks-panel border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-body-secondary bg-opacity-50 border-bottom py-3">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1" style="min-width:min(100%, 14rem);">
                        <h5 class="mb-1 d-flex align-items-center gap-2 flex-wrap">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success p-2"><i class="ti ti-math-function"></i></span>
                            Nilai pelamar teragregasi (KMKK → TOPSIS)
                        </h5>
                        <p class="text-muted small mb-0">
                            Nilai tiap sel = agregasi KMKK untuk kriteria tersebut. Kolom kanan menghitung Σ (nilai × bobot AHP) sebagai pratinjau; nilai preferensi akhir ada di blok peringkat TOPSIS di bawah.
                        </p>
                    </div>
                    @if($aggregatesByApplicant->isEmpty())
                    <span class="badge rounded-pill bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-25 align-self-center">Belum ada agregasi</span>
                    @elseif($focusApplicantId ?? null)
                    <span class="badge rounded-pill bg-white text-dark border fw-normal px-3 py-2 align-self-center">1 pelamar dipilih</span>
                    @else
                    <span class="badge rounded-pill bg-light border text-muted fw-normal px-3 py-2 align-self-center">{{ $aggregatesByApplicant->count() }} pelamar teragregasi</span>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                @if($reportApplicants->isEmpty())
                <div class="text-center text-muted py-5 px-4">
                    <i class="ti ti-users d-block mb-2 fs-2 opacity-50"></i>
                    <p class="mb-0">Tidak ada pelamar terdaftar pada periode ini.</p>
                </div>
                @else
                @php $_repAggCols = 3 + $criteria->count(); @endphp
                <div class="table-responsive rounded-bottom">
                    <table class="table table-sm table-hover table-striped mb-0 align-middle">
                        <thead class="table-light border-bottom report-inline-filter-row">
                            <tr>
                                <th colspan="{{ $_repAggCols }}" class="border-bottom px-3 py-2 bg-body-secondary bg-opacity-40">
                                    <form method="GET" class="row g-2 align-items-center gy-3 mb-0">
                                        <input type="hidden" name="period_id" value="{{ $periodId }}">
                                        <div class="col-auto"><span class="small text-muted text-nowrap"><i class="ti ti-filter me-1"></i>Pelamar</span></div>
                                        <div class="col col-sm-7 col-md-5 col-lg-4">
                                            <select name="applicant_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">— Pilih nama —</option>
                                                @foreach($reportApplicants as $applicant)
                                                <option value="{{ $applicant->id }}" @selected(($focusApplicantId ?? null) == $applicant->id)>{{ $applicant->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12"><span class="text-muted small">Filter di baris atas tabel ini untuk melihat satu pelamar pada bagian laporan ini.</span></div>
                                    </form>
                                </th>
                            </tr>
                            <tr>
                                <th class="ps-4 fw-normal">No</th>
                                <th class="fw-normal" style="min-width:11rem;">Nama pelamar</th>
                                @foreach($criteria as $criterion)
                                <th class="text-end" title="{{ $criterion->name }}">
                                    <span class="badge bg-light-secondary text-secondary-emphasis">{{ $criterion->code }}</span>
                                </th>
                                @endforeach
                                <th class="text-end pe-4 fw-normal" title="Penjumlahan terbobot (bukan skor TOPSIS)">Σ terbobot</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @if($aggregatesByApplicant->isEmpty())
                            <tr>
                                <td colspan="{{ $_repAggCols }}" class="text-center text-muted py-5 px-4">
                                    <i class="ti ti-table-off d-block mb-2 fs-3 opacity-50"></i>
                                    <p class="mb-0 small">Belum ada matriks agregasi. Jalankan KMKK di halaman Evaluasi Kelompok.</p>
                                </td>
                            </tr>
                            @elseif(!($focusApplicantId ?? null))
                            <tr>
                                <td colspan="{{ $_repAggCols }}" class="text-center text-muted py-4 px-4">
                                    <i class="ti ti-user-check d-block mb-2 fs-3 opacity-25"></i>
                                    <p class="mb-0 small">Pilih pelamar pada baris filter di atas tabel.</p>
                                </td>
                            </tr>
                            @else
                            @php
                                $focusedApplicant = $reportApplicants->firstWhere('id', $focusApplicantId);
                                $aggRows = $focusedApplicant ? ($aggregatesByApplicant->get($focusedApplicant->id) ?? collect()) : collect();
                                $weighted = 0;
                                if ($focusedApplicant) {
                                    foreach ($criteria as $criterion) {
                                        $w = $weights->get($criterion->id);
                                        $cell = $aggRows->firstWhere('criteria_id', $criterion->id);
                                        if ($w && $cell) {
                                            $weighted += (float) $cell->aggregated_score * (float) $w->weight;
                                        }
                                    }
                                }
                            @endphp
                            @if(!$focusedApplicant)
                            <tr><td colspan="{{ $_repAggCols }}" class="text-center text-muted py-4">Pelamar tidak ditemukan.</td></tr>
                            @else
                            <tr>
                                <td class="ps-4 text-muted">1</td>
                                <td class="fw-semibold">{{ $focusedApplicant->name }}</td>
                                @foreach($criteria as $criterion)
                                @php $cell = $aggRows->firstWhere('criteria_id', $criterion->id); @endphp
                                <td class="text-end">
                                    @if($cell)
                                    <div class="d-flex flex-column align-items-end gap-1 py-1">
                                        <code class="report-matriks-num small bg-light px-2 py-1 rounded">{{ number_format((float) $cell->aggregated_score, 4, ',', '.') }}</code>
                                        <span class="badge rounded-pill bg-light border text-secondary fw-normal" style="font-size:10px;" title="Evaluator dipakai agregasi">n={{ $cell->evaluator_count_used }}</span>
                                    </div>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @endforeach
                                <td class="text-end pe-4"><strong class="report-matriks-num small">{{ number_format($weighted, 6, ',', '.') }}</strong></td>
                            </tr>
                            @endif
                            @endif
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-3 report-matriks-panel border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-body-secondary bg-opacity-50 border-bottom py-3">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1" style="min-width:min(100%, 14rem);">
                        <h5 class="mb-1 d-flex align-items-center gap-2 flex-wrap">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary p-2"><i class="ti ti-list-details"></i></span>
                            Rincian penilaian per evaluator (mentah)
                        </h5>
                        <p class="text-muted small mb-0">Penilaian evaluator sebelum agregasi. Pilih pelamar lewat baris filter di atas tabel di bawah.</p>
                    </div>
                    @if($focusApplicantId ?? null)
                        @if(isset($evaluations) && $evaluations->isNotEmpty())
                        <span class="badge rounded-pill bg-white text-dark border fw-normal px-3 py-2 align-self-center">{{ $evaluations->count() }} baris</span>
                        @else
                        <span class="badge rounded-pill bg-light border text-muted fw-normal px-3 py-2 align-self-center">0 baris</span>
                        @endif
                    @elseif(($evaluationsPeriodTotal ?? 0) > 0)
                    <span class="badge rounded-pill bg-light border text-muted fw-normal px-3 py-2 align-self-center">Total periode: {{ number_format((int) $evaluationsPeriodTotal, 0, ',', '.') }} baris</span>
                    @else
                    <span class="badge rounded-pill bg-light border text-muted fw-normal px-3 py-2 align-self-center">0 baris</span>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                @if($reportApplicants->isEmpty())
                <div class="text-center text-muted py-5 px-4">
                    <i class="ti ti-users d-block mb-2 fs-2 opacity-50"></i>
                    <p class="mb-0">Tidak ada pelamar terdaftar pada periode ini.</p>
                </div>
                @else
                @php $_repRawCols = 5; @endphp
                <div class="table-responsive rounded-bottom">
                    <table class="table table-sm table-striped table-hover mb-0 align-middle">
                        <thead class="table-light border-bottom report-inline-filter-row">
                            <tr>
                                <th colspan="{{ $_repRawCols }}" class="border-bottom px-3 py-2 bg-body-secondary bg-opacity-40">
                                    <form method="GET" class="row g-2 align-items-center gy-3 mb-0">
                                        <input type="hidden" name="period_id" value="{{ $periodId }}">
                                        <div class="col-auto"><span class="small text-muted text-nowrap"><i class="ti ti-filter me-1"></i>Pelamar</span></div>
                                        <div class="col col-sm-7 col-md-5 col-lg-4">
                                            <select name="applicant_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">— Pilih nama —</option>
                                                @foreach($reportApplicants as $applicant)
                                                <option value="{{ $applicant->id }}" @selected(($focusApplicantId ?? null) == $applicant->id)>{{ $applicant->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12"><span class="text-muted small">Menampilkan mentah hanya untuk pelamar terpilih pada tabel ini.</span></div>
                                    </form>
                                </th>
                            </tr>
                            <tr>
                                <th class="ps-4" style="width:3rem;">#</th>
                                <th style="min-width:11rem;">Pelamar</th>
                                <th style="min-width:14rem;">Kriteria</th>
                                <th style="min-width:10rem;">Evaluator</th>
                                <th class="text-end pe-4" style="width:6rem;">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @if(($evaluationsPeriodTotal ?? 0) === 0)
                            <tr>
                                <td colspan="{{ $_repRawCols }}" class="text-center text-muted py-5 px-4">
                                    <i class="ti ti-database-off d-block mb-2 fs-2 opacity-50"></i>
                                    <p class="mb-0">Belum ada penilaian mentah untuk periode ini.</p>
                                </td>
                            </tr>
                            @elseif(!$focusApplicantId && ($evaluationsPeriodTotal ?? 0) > 0)
                            <tr>
                                <td colspan="{{ $_repRawCols }}" class="text-center text-muted py-4 px-4">
                                    <i class="ti ti-user-check d-block mb-2 fs-3 opacity-25"></i>
                                    <p class="mb-1 fw-medium text-body">Pilih pelamar pada baris filter di atas tabel.</p>
                                    <p class="small mb-0 mx-auto text-muted" style="max-width:26rem;">Total baris penilaian dalam periode saat ini: {{ number_format((int) $evaluationsPeriodTotal, 0, ',', '.') }}.</p>
                                </td>
                            </tr>
                            @elseif($evaluations->isEmpty())
                            <tr>
                                <td colspan="{{ $_repRawCols }}" class="text-center text-muted py-4 px-4">
                                    <i class="ti ti-list-search d-block mb-2 fs-2 opacity-50"></i>
                                    <p class="mb-0">Tidak ada baris penilaian mentah untuk pelamar yang dipilih.</p>
                                </td>
                            </tr>
                            @else
                            @foreach($evaluations as $ev)
                            <tr>
                                <td class="ps-4 text-muted small">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $ev->applicant?->name ?? '—' }}</td>
                                <td>
                                    @if($ev->criteria)
                                    <span class="badge rounded-pill bg-light-secondary text-secondary-emphasis">{{ $ev->criteria->code }}</span>
                                    <span class="d-block small text-body mt-1">{{ $ev->criteria->name }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $ev->evaluator?->name ?? '—' }}</span>
                                    @if($ev->evaluator?->role_label)
                                    <span class="d-block small text-muted">{{ $ev->evaluator->role_label }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4"><code class="report-matriks-num small bg-light px-2 py-1 rounded">{{ number_format((float) $ev->score, 2, ',', '.') }}</code></td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="mb-0"><i class="ti ti-trophy me-2"></i>Hasil akhir seleksi</h5>
                <a href="{{ route('reports.print', $selectedPeriod) }}" target="_blank" rel="noopener" class="btn btn-primary">
                    <i class="ti ti-printer me-1"></i>Cetak laporan
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless mb-0 align-middle">
                        <thead class="border-bottom">
                            <tr>
                                <th class="ps-4" style="width:5rem;">Rank</th>
                                <th>Nama</th>
                                <th class="text-end">Nilai preferensi</th>
                                <th class="text-end">D+</th>
                                <th class="text-end">D−</th>
                                <th class="pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                            <tr>
                                <td class="ps-4">
                                    <div class="avtar avtar-xs bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold text-primary">{{ $result->rank }}</div>
                                </td>
                                <td class="fw-medium">{{ $result->applicant->name }}</td>
                                <td class="text-end"><code class="small bg-light px-2 py-1 rounded">{{ number_format((float) $result->preference_value, 6, ',', '.') }}</code></td>
                                <td class="text-end"><code class="small text-muted">{{ number_format((float) $result->positive_distance, 6, ',', '.') }}</code></td>
                                <td class="text-end"><code class="small text-muted">{{ number_format((float) $result->negative_distance, 6, ',', '.') }}</code></td>
                                <td class="pe-4">{!! $result->status_badge !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @elseif($selectedPeriod && $results->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 px-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light-warning text-warning mb-3" style="width:4rem;height:4rem;">
                    <i class="ti ti-chart-arrows f-28"></i>
                </div>
                <h5 class="mb-2">Belum ada hasil seleksi</h5>
                <p class="text-muted mb-0 mx-auto" style="max-width:28rem;">Periode <strong>{{ $selectedPeriod->name }}</strong> belum memiliki data peringkat. Jalankan perhitungan TOPSIS terlebih dahulu.</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
