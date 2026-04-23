@extends('BE.layouts.main')
@section('title', 'Hasil Perangkingan')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('calculations.results') }}">Perhitungan</a></li>
                            <li class="breadcrumb-item" aria-current="page">Hasil</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Hasil Perangkingan</h2>
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
            @if($results->isEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5 px-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-body p-4 mb-3">
                            <i class="ti ti-database-off text-muted" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="mb-2">Belum ada data hasil</h5>
                        <p class="text-muted mb-0">Belum ada data hasil perangkingan untuk periode <strong>{{ $selectedPeriod->name }}</strong>. Jalankan perhitungan TOPSIS terlebih dahulu.</p>
                    </div>
                </div>
            @else
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-trophy text-primary"></i>
                            <h5 class="mb-0">Ringkasan</h5>
                        </div>
                        <span class="badge bg-light-secondary text-secondary">{{ $selectedPeriod->name }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0 align-middle datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 4rem;">Rank</th>
                                        <th>Nama Pelamar</th>
                                        <th class="text-end">D+</th>
                                        <th class="text-end">D−</th>
                                        <th class="text-end">Nilai Preferensi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results as $result)
                                        @php
                                            $isLulus = $result->status === 'lulus';
                                            $isFirst = (int) $result->rank === 1;
                                        @endphp
                                        <tr class="{{ $isLulus ? 'table-success' : '' }}">
                                            <td class="text-center">
                                                @if($isFirst)
                                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning text-dark fw-bold shadow-sm" style="width: 2.25rem; height: 2.25rem;" title="Peringkat 1">
                                                        <i class="ti ti-trophy"></i>
                                                    </span>
                                                @else
                                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-semibold" style="width: 2.25rem; height: 2.25rem; font-size: 0.875rem;">{{ $result->rank }}</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap fw-medium">{{ $result->applicant->name ?? '—' }}</td>
                                            <td class="text-end"><span class="text-muted small">{{ number_format((float) $result->positive_distance, 4) }}</span></td>
                                            <td class="text-end"><span class="text-muted small">{{ number_format((float) $result->negative_distance, 4) }}</span></td>
                                            <td class="text-end"><code class="small user-select-all">{{ number_format((float) $result->preference_value, 4) }}</code></td>
                                            <td>
                                                @if($isLulus)
                                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Lulus</span>
                                                @else
                                                    <span class="badge bg-light-danger text-danger"><i class="ti ti-x me-1"></i>Tidak lulus</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
