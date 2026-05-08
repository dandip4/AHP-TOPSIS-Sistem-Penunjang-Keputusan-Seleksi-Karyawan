@extends('BE.layouts.main')
@section('title', 'Detail Periode Seleksi')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('periods.index') }}">Periode Seleksi</a></li>
                            <li class="breadcrumb-item" aria-current="page">Detail</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h2 class="mb-0">Detail Periode Seleksi</h2>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('periods.edit', $period) }}" class="btn btn-primary btn-sm">
                                    <i class="ti ti-edit me-1"></i>Edit
                                </a>
                                <a href="{{ route('periods.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-arrow-left me-1"></i>Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0"><i class="ti ti-calendar-event me-2"></i>Informasi Periode</h5>
                        @switch($period->status)
                            @case('draft')
                                <span class="badge bg-light-secondary">Draft</span>
                                @break
                            @case('open')
                                <span class="badge bg-light-success">Dibuka</span>
                                @break
                            @case('closed')
                                <span class="badge bg-light-warning">Ditutup</span>
                                @break
                            @case('completed')
                                <span class="badge bg-light-primary">Selesai</span>
                                @break
                            @default
                                <span class="badge bg-light-secondary">—</span>
                        @endswitch
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 col-xl-4">
                                <div class="bg-body rounded p-3 h-100 border">
                                    <small class="text-muted d-block mb-1">Nama Periode</small>
                                    <div class="fw-semibold mb-0">{{ $period->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="bg-body rounded p-3 h-100 border">
                                    <small class="text-muted d-block mb-1">Posisi</small>
                                    <div class="fw-semibold mb-0">{{ $period->position }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="bg-body rounded p-3 h-100 border">
                                    <small class="text-muted d-block mb-1">Dibuat oleh</small>
                                    <div class="fw-semibold mb-0">{{ $period->creator?->name ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="bg-body rounded p-3 h-100 border">
                                    <small class="text-muted d-block mb-1">Tanggal Mulai</small>
                                    <div class="fw-semibold mb-0 d-flex align-items-center gap-2">
                                        <i class="ti ti-calendar-event text-muted"></i>
                                        {{ $period->start_date->locale('id')->translatedFormat('d F Y') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="bg-body rounded p-3 h-100 border">
                                    <small class="text-muted d-block mb-1">Tanggal Selesai</small>
                                    <div class="fw-semibold mb-0 d-flex align-items-center gap-2">
                                        <i class="ti ti-calendar-event text-muted"></i>
                                        {{ $period->end_date->locale('id')->translatedFormat('d F Y') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="bg-body rounded p-3 border">
                                    <small class="text-muted d-block mb-1">Deskripsi</small>
                                    <p class="mb-0">{{ $period->description ?: '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom-0 pb-0">
                        <ul class="nav nav-tabs border-0 mb-0" id="periodDetailTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-pelamar" data-bs-toggle="tab" data-bs-target="#pane-pelamar" type="button" role="tab" aria-controls="pane-pelamar" aria-selected="true">
                                    <i class="ti ti-users me-2"></i>Pelamar
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-bobot" data-bs-toggle="tab" data-bs-target="#pane-bobot" type="button" role="tab" aria-controls="pane-bobot" aria-selected="false">
                                    <i class="ti ti-scale me-2"></i>Bobot Kriteria
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-hasil" data-bs-toggle="tab" data-bs-target="#pane-hasil" type="button" role="tab" aria-controls="pane-hasil" aria-selected="false">
                                    <i class="ti ti-trophy me-2"></i>Hasil Seleksi
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body pt-3">
                        <div class="tab-content" id="periodDetailTabContent">
                            <div class="tab-pane fade show active" id="pane-pelamar" role="tabpanel" aria-labelledby="tab-pelamar" tabindex="0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 text-muted fw-normal">No</th>
                                                <th class="text-muted fw-normal">Nama</th>
                                                <th class="text-muted fw-normal">Email</th>
                                                <th class="text-muted fw-normal">Telepon</th>
                                                <th class="pe-4 text-muted fw-normal">Jenis Kelamin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($period->applicants as $applicant)
                                            <tr>
                                                <td class="ps-4 align-middle">{{ $loop->iteration }}</td>
                                                <td class="align-middle">{{ $applicant->name }}</td>
                                                <td class="align-middle">{{ $applicant->email }}</td>
                                                <td class="align-middle">{{ $applicant->phone ?? '—' }}</td>
                                                <td class="pe-4 align-middle">{{ $applicant->gender_label }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4 ps-4 pe-4">Belum ada pelamar</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pane-bobot" role="tabpanel" aria-labelledby="tab-bobot" tabindex="0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 text-muted fw-normal">No</th>
                                                <th class="text-muted fw-normal">Kode</th>
                                                <th class="text-muted fw-normal">Nama Kriteria</th>
                                                <th class="pe-4 text-muted fw-normal">Bobot</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($period->linkedCriteria as $crit)
                                            @php $cw = $period->criteriaWeights->firstWhere('criteria_id', $crit->id); @endphp
                                            <tr>
                                                <td class="ps-4 align-middle">{{ $loop->iteration }}</td>
                                                <td class="align-middle">
                                                    @if($crit->code)
                                                        <span class="badge bg-light-primary">{{ $crit->code }}</span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="align-middle">{{ $crit->name }}</td>
                                                <td class="pe-4 align-middle">{{ $cw ? number_format((float) $cw->weight, 6) : '—' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4 ps-4 pe-4">Belum ada bobot kriteria</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pane-hasil" role="tabpanel" aria-labelledby="tab-hasil" tabindex="0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 text-muted fw-normal">Rank</th>
                                                <th class="text-muted fw-normal">Nama Pelamar</th>
                                                <th class="text-muted fw-normal">Nilai Preferensi</th>
                                                <th class="pe-4 text-muted fw-normal">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($period->selectionResults->sortBy('rank') as $result)
                                            <tr>
                                                <td class="ps-4 align-middle"><span class="badge bg-light-primary">#{{ $result->rank }}</span></td>
                                                <td class="align-middle">{{ $result->applicant?->name ?? '—' }}</td>
                                                <td class="align-middle">{{ number_format((float) $result->preference_value, 4) }}</td>
                                                <td class="pe-4 align-middle">{!! $result->status_badge !!}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4 ps-4 pe-4">Belum ada hasil seleksi</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
