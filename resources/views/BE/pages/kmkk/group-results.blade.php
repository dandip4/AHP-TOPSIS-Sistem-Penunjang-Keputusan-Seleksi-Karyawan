@extends('BE.layouts.main')
@section('title', 'Evaluasi Kelompok (KMKK)')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page">KMKK</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Evaluasi Kelompok (KMKK)</h2>
                            <p class="text-muted mb-0 small">Agregasi penilaian multi-evaluator (Yager OWA atau rata-rata) sebagai input TOPSIS</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="mb-0"><i class="ti ti-filter me-2"></i>Filter periode</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small text-muted mb-1">Periode seleksi</label>
                        <select name="period_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">— Pilih periode —</option>
                            @foreach($periods as $period)
                            <option value="{{ $period->id }}" @selected($periodId == $period->id)>{{ $period->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if($periodId && $selectedPeriod)
        <style>
            .kmkk-table-panel .table thead th {
                font-size: .72rem;
                text-transform: uppercase;
                letter-spacing: .04em;
                color: var(--bs-secondary-color);
                white-space: nowrap;
            }
            .kmkk-table-panel tbody td {
                vertical-align: middle;
            }
            .kmkk-num {
                font-variant-numeric: tabular-nums;
            }
            .kmkk-table-panel .kmkk-inline-filter-row th {
                font-weight: 400;
                vertical-align: middle;
            }
        </style>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Status agregasi</h6>
                        @if($aggregateComplete)
                        <span class="badge bg-success">Matriks agregat lengkap</span>
                        @if($selectedPeriod->aggregation_computed_at)
                        <p class="small text-muted mb-0 mt-2">Terakhir: {{ $selectedPeriod->aggregation_computed_at->format('d M Y H:i') }}</p>
                        @endif
                        <p class="small mb-0 mt-2">Metode tersimpan: <strong>{{ $selectedPeriod->aggregation_method === 'owa_most' ? 'OWA (Yager)' : 'Rata-rata' }}</strong></p>
                        @else
                        <span class="badge bg-warning text-dark">Belum lengkap / belum dibangun</span>
                        <p class="small text-muted mb-0 mt-2">Semua sel pelamar × kriteria harus punya minimal satu nilai dari evaluator, lalu admin menjalankan agregasi.</p>
                        @endif
                    </div>
                </div>
            </div>
            @if(Auth::user()->isAdmin())
            <div class="col-md-8">
                <div class="card h-100 border-primary border-opacity-25">
                    <div class="card-header bg-primary-subtle">
                        <h6 class="mb-0"><i class="ti ti-users-group me-2"></i>Bangun ulang matriks agregat</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('kmkk.rebuild') }}" class="row g-3 align-items-end">
                            @csrf
                            <input type="hidden" name="period_id" value="{{ $periodId }}">
                            <div class="col-md-5">
                                <label class="form-label small mb-1">Metode agregasi</label>
                                <select name="aggregation_method" class="form-select">
                                    <option value="average" @selected(($selectedPeriod->aggregation_method ?? 'average') === 'average')>Rata-rata aritmetik</option>
                                    <option value="owa_most" @selected(($selectedPeriod->aggregation_method ?? '') === 'owa_most')>OWA — kuantifikasi linguistik Q(r)=r<sup>α</sup> (Yager)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">α OWA (&gt;1)</label>
                                <input type="number" step="0.01" min="1.01" name="owa_alpha" class="form-control" value="{{ old('owa_alpha', $selectedPeriod->owa_alpha ?? 2) }}">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-hierarchy me-1"></i>Hitung matriks agregat
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <div class="col-md-8">
                <div class="card border-0 bg-body-secondary h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="ti ti-info-circle fs-4 me-3 text-primary"></i>
                        <p class="mb-0 small">Hanya administrator yang dapat menjalankan agregasi kelompok. Anda dapat melihat penilaian per evaluator pada tabel di bawah.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="card mb-3 kmkk-table-panel border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-body-secondary bg-opacity-50 border-bottom py-3">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1" style="min-width:min(100%, 14rem);">
                        <h5 class="mb-1 d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary p-2"><i class="ti ti-list-details"></i></span>
                            Rincian penilaian per evaluator (mentah)
                        </h5>
                        <p class="text-muted small mb-0">Satu baris = satu skor untuk pasangan pelamar–kriteria dari evaluator yang bersangkutan.</p>
                    </div>
                    @if(($focusApplicantId ?? null) && !$rawEvaluations->isEmpty())
                    <span class="badge align-self-center rounded-pill bg-white text-dark border fw-normal px-3 py-2">{{ $rawEvaluations->count() }} baris untuk pelamar ini</span>
                    @elseif(($focusApplicantId ?? null) && $rawEvaluationsTotal > 0)
                    <span class="badge align-self-center rounded-pill bg-light border text-muted fw-normal px-3 py-2">Total periode: {{ number_format((int) $rawEvaluationsTotal, 0, ',', '.') }} baris</span>
                    @elseif($rawEvaluationsTotal > 0)
                    <span class="badge align-self-center rounded-pill bg-light border text-muted fw-normal px-3 py-2">Total periode: {{ number_format((int) $rawEvaluationsTotal, 0, ',', '.') }} baris</span>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                @if($applicants->isEmpty())
                <div class="text-center text-muted py-5 px-4">
                    <i class="ti ti-users d-block mb-2 fs-2 opacity-50"></i>
                    <p class="mb-0">Belum ada pelamar terdaftar pada periode ini.</p>
                </div>
                @else
                @php $_kmkkRawCols = 5; @endphp
                <div class="table-responsive rounded-bottom">
                    <table class="table table-sm table-striped table-hover mb-0 align-middle">
                        <thead class="table-light border-bottom kmkk-inline-filter-row">
                            <tr>
                                <th colspan="{{ $_kmkkRawCols }}" class="border-bottom px-3 py-2 bg-body-secondary bg-opacity-40">
                                    <form method="GET" class="row g-2 align-items-center gy-3 mb-0">
                                        <input type="hidden" name="period_id" value="{{ $periodId }}">
                                        <div class="col-auto">
                                            <span class="small text-muted text-nowrap"><i class="ti ti-filter me-1"></i>Pelamar</span>
                                        </div>
                                        <div class="col col-sm-7 col-md-5 col-lg-4">
                                            <select name="applicant_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">— Pilih nama —</option>
                                                @foreach($applicants as $applicant)
                                                <option value="{{ $applicant->id }}" @selected(($focusApplicantId ?? null) == $applicant->id)>{{ $applicant->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <span class="text-muted small">Pilih satu pelamar untuk baris data di bawah.</span>
                                        </div>
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
                            @if(!$focusApplicantId)
                            <tr>
                                <td colspan="{{ $_kmkkRawCols }}" class="text-center text-muted py-4 px-4">
                                    <i class="ti ti-user-check d-block mb-2 fs-3 opacity-25"></i>
                                    <p class="mb-1 fw-medium text-body">Pilih pelamar pada baris filter di atas tabel.</p>
                                    <p class="small mb-0 mx-auto" style="max-width:26rem;">Satu pelamar per tampilan agar halaman tetap ringkas.</p>
                                </td>
                            </tr>
                            @elseif($rawEvaluationsTotal === 0)
                            <tr>
                                <td colspan="{{ $_kmkkRawCols }}" class="text-center text-muted py-4 px-4">
                                    <i class="ti ti-database-off d-block mb-2 fs-2 opacity-50"></i>
                                    <p class="mb-0">Belum ada baris penilaian untuk periode ini.</p>
                                </td>
                            </tr>
                            @elseif($rawEvaluations->isEmpty())
                            <tr>
                                <td colspan="{{ $_kmkkRawCols }}" class="text-center text-muted py-4 px-4">
                                    <i class="ti ti-list-search d-block mb-2 fs-2 opacity-50"></i>
                                    <p class="mb-0">Tidak ada penilaian mentah untuk pelamar yang dipilih.</p>
                                </td>
                            </tr>
                            @else
                            @foreach($rawEvaluations as $ev)
                            <tr>
                                <td class="ps-4 text-muted small">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $ev->applicant?->name ?? '—' }}</td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        @if($ev->criteria)
                                        <span class="badge rounded-pill bg-light-secondary text-secondary-emphasis align-self-start">{{ $ev->criteria->code }}</span>
                                        <span class="small text-body">{{ $ev->criteria->name }}</span>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $ev->evaluator?->name ?? '—' }}</span>
                                    @if($ev->evaluator?->role_label)
                                    <span class="d-block small text-muted">{{ $ev->evaluator->role_label }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4"><code class="kmkk-num small bg-light px-2 py-1 rounded">{{ number_format((float) $ev->score, 2, ',', '.') }}</code></td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-4 kmkk-table-panel border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-body-secondary bg-opacity-50 border-bottom py-3">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1" style="min-width:min(100%, 14rem);">
                        <h5 class="mb-1 d-flex align-items-center gap-2 flex-wrap">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success p-2"><i class="ti ti-math-function"></i></span>
                            Nilai teragregasi (KMKK → TOPSIS)
                        </h5>
                        <p class="text-muted small mb-0">Matriks keputusan setelah agregasi kelompok. Angka per sel memakai format Indonesia; label <span class="badge border bg-light text-muted fw-normal px-1">n</span> = jumlah evaluator yang dipakai.</p>
                    </div>
                    <a href="{{ route('calculations.topsis', ['period_id' => $periodId]) }}" class="btn btn-sm btn-primary align-self-center flex-shrink-0">
                        <i class="ti ti-chart-bar me-1"></i>Lanjut TOPSIS
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($applicants->isEmpty())
                <div class="text-center text-muted py-5 px-4">
                    <i class="ti ti-users d-block mb-2 fs-2 opacity-50"></i>
                    <p class="mb-0">Belum ada pelamar terdaftar pada periode ini.</p>
                </div>
                @else
                @php $_kmkkAggCols = 1 + $criteria->count(); @endphp
                <div class="table-responsive rounded-bottom">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light border-bottom kmkk-inline-filter-row">
                            <tr>
                                <th colspan="{{ $_kmkkAggCols }}" class="border-bottom px-3 py-2 bg-body-secondary bg-opacity-40">
                                    <form method="GET" class="row g-2 align-items-center gy-3 mb-0">
                                        <input type="hidden" name="period_id" value="{{ $periodId }}">
                                        <div class="col-auto">
                                            <span class="small text-muted text-nowrap"><i class="ti ti-filter me-1"></i>Pelamar</span>
                                        </div>
                                        <div class="col col-sm-7 col-md-5 col-lg-4">
                                            <select name="applicant_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">— Pilih nama —</option>
                                                @foreach($applicants as $applicant)
                                                <option value="{{ $applicant->id }}" @selected(($focusApplicantId ?? null) == $applicant->id)>{{ $applicant->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <span class="text-muted small">Pilih satu pelamar untuk baris agregasi di bawah.</span>
                                        </div>
                                    </form>
                                </th>
                            </tr>
                            <tr>
                                <th class="ps-4" style="min-width:11rem;">Pelamar</th>
                                @foreach($criteria as $criterion)
                                <th class="text-end text-nowrap" title="{{ $criterion->name }}">
                                    <span class="badge bg-light-primary text-primary-emphasis">{{ $criterion->code }}</span>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @if($criteria->isEmpty())
                            <tr>
                                <td colspan="{{ $_kmkkAggCols }}" class="text-center text-muted py-4">Tidak ada kriteria untuk periode ini.</td>
                            </tr>
                            @elseif($aggregatesByApplicant->isEmpty())
                            <tr>
                                <td colspan="{{ $_kmkkAggCols }}" class="text-center text-muted py-4 px-4">
                                    <i class="ti ti-table-off d-block mb-2 fs-3 opacity-50"></i>
                                    <p class="mb-0 small">Matriks agregat belum ada. Jalankan <strong>Hitung matriks agregat</strong> (admin).</p>
                                </td>
                            </tr>
                            @elseif(!$focusApplicantId)
                            <tr>
                                <td colspan="{{ $_kmkkAggCols }}" class="text-center text-muted py-4 px-4">
                                    <i class="ti ti-user-check d-block mb-2 fs-3 opacity-25"></i>
                                    <p class="mb-0 small">Pilih pelamar pada baris filter di atas.</p>
                                </td>
                            </tr>
                            @else
                            @php $applicantAgg = $applicants->firstWhere('id', $focusApplicantId); @endphp
                            @if(!$applicantAgg)
                            <tr>
                                <td colspan="{{ $_kmkkAggCols }}" class="text-center text-muted py-4">Pelamar tidak ditemukan.</td>
                            </tr>
                            @else
                            @php
                                $cells = ($aggregatesByApplicant instanceof \Illuminate\Support\Collection ? $aggregatesByApplicant->get($applicantAgg->id) : null) ?? collect();
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $applicantAgg->name }}</td>
                                @foreach($criteria as $criterion)
                                @php
                                    $agg = $cells instanceof \Illuminate\Support\Collection ? $cells->firstWhere('criteria_id', $criterion->id) : null;
                                @endphp
                                <td class="text-end pe-2">
                                    @if($agg)
                                    <div class="d-flex flex-column align-items-end gap-1 py-1">
                                        <code class="kmkk-num small bg-body-secondary px-2 py-1 rounded mb-0">{{ number_format((float) $agg->aggregated_score, 4, ',', '.') }}</code>
                                        <span class="badge rounded-pill bg-light border text-secondary fw-normal" style="font-size:10px;" title="Jumlah evaluator dipakai">n={{ $agg->evaluator_count_used }}</span>
                                    </div>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endif
                            @endif
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
