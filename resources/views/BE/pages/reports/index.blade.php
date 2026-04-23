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
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Periode seleksi</label>
                        <select name="period_id" class="form-select" onchange="this.form.submit()">
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

        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="mb-0"><i class="ti ti-table me-2"></i>Nilai pelamar</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-sm mb-0 align-middle">
                        <thead class="border-bottom">
                            <tr>
                                <th class="ps-4 text-muted fw-normal" style="width:3rem;">No</th>
                                <th>Nama pelamar</th>
                                @foreach($criteria as $criterion)
                                <th class="text-end"><span class="badge bg-light-secondary">{{ $criterion->code }}</span></th>
                                @endforeach
                                <th class="text-end pe-4">Total terbobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evaluations as $applicantId => $applicantEvals)
                            @php
                                $applicant = optional($applicantEvals->first())->applicant;
                                $weighted = 0;
                                foreach ($criteria as $criterion) {
                                    $w = $weights->get($criterion->id);
                                    $ev = $applicantEvals->firstWhere('criteria_id', $criterion->id);
                                    if ($w && $ev) {
                                        $weighted += (float) $ev->score * (float) $w->weight;
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                                <td class="fw-medium">{{ $applicant->name ?? '—' }}</td>
                                @foreach($criteria as $criterion)
                                @php $ev = $applicantEvals->firstWhere('criteria_id', $criterion->id); @endphp
                                <td class="text-end font-monospace small">{{ $ev ? number_format((float) $ev->score, 4, ',', '.') : '—' }}</td>
                                @endforeach
                                <td class="text-end pe-4 font-monospace small fw-semibold">{{ number_format($weighted, 6, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="{{ 3 + $criteria->count() }}" class="text-center text-muted py-4">Belum ada data penilaian</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
