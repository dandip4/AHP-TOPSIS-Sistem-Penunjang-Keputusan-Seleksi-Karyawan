@extends('BE.layouts.main')
@section('title', 'Penilaian Pelamar')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page">Penilaian Pelamar</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Penilaian Pelamar</h2>
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
            @if($applicants->isEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5 px-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-body p-4 mb-3">
                            <i class="ti ti-users-off text-muted" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="mb-2">Belum ada pelamar</h5>
                        <p class="text-muted mb-0">Tidak ada pelamar pada periode <strong>{{ $selectedPeriod->name }}</strong>.</p>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('evaluations.store') }}">
                    @csrf
                    <input type="hidden" name="period_id" value="{{ $periodId }}">

                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 position-sticky top-0 border-bottom bg-body py-3" style="z-index: 1020;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-math-function text-primary"></i>
                                <h5 class="mb-0">Matriks Penilaian</h5>
                                <span class="badge bg-light-secondary text-secondary ms-1">{{ $selectedPeriod->name }}</span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm">
                                <i class="ti ti-device-floppy me-2"></i>Simpan Penilaian
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-nowrap">Nama Pelamar</th>
                                            @foreach($criteria as $criterion)
                                                <th class="text-center text-nowrap">
                                                    <span class="d-block fw-bold">{{ $criterion->code }}</span>
                                                    <small class="text-muted fw-normal">{{ $criterion->name }}</small>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($applicants as $applicant)
                                            @php
                                                $appEvals = $evaluations->get($applicant->id) ?? collect();
                                            @endphp
                                            <tr>
                                                <td class="text-nowrap fw-medium">{{ $applicant->name }}</td>
                                                @foreach($criteria as $criterion)
                                                    @php
                                                        $evalRow = $appEvals->firstWhere('criteria_id', $criterion->id);
                                                        $selectedScore = $evalRow ? (int) $evalRow->score : null;
                                                    @endphp
                                                    <td class="text-center align-middle" style="min-width: 7rem;">
                                                        <select
                                                            name="scores[{{ $applicant->id }}][{{ $criterion->id }}]"
                                                            class="form-select form-select-sm"
                                                            required
                                                        >
                                                            <option value="" disabled {{ $selectedScore === null ? 'selected' : '' }}>—</option>
                                                            @if($criterion->subCriteria->isNotEmpty())
                                                                @foreach($criterion->subCriteria->sortBy('value') as $sub)
                                                                    <option value="{{ $sub->value }}" @selected($selectedScore === (int) $sub->value)>
                                                                        {{ $sub->name }} ({{ $sub->value }})
                                                                    </option>
                                                                @endforeach
                                                            @else
                                                                @for($v = 1; $v <= 5; $v++)
                                                                    <option value="{{ $v }}" @selected($selectedScore === $v)>{{ $v }}</option>
                                                                @endfor
                                                            @endif
                                                        </select>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer d-md-none d-flex justify-content-end border-top bg-body position-sticky bottom-0 py-3 shadow-sm" style="z-index: 1010;">
                            <button type="submit" class="btn btn-primary btn-lg px-4 w-100">
                                <i class="ti ti-device-floppy me-2"></i>Simpan Penilaian
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        @endif
    </div>
</div>
@endsection
