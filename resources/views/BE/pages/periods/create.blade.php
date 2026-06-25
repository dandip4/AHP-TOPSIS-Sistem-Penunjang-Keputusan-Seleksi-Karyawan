@extends('BE.layouts.main')
@section('title', 'Tambah Periode Seleksi')
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
                            <li class="breadcrumb-item" aria-current="page">Tambah</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Tambah Periode Seleksi</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-xl-10 col-xxl-8">
                <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0"><i class="ti ti-calendar-event me-2"></i>Data Periode</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('periods.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Nama Periode" required>
                                        <label for="name">Nama Periode</label>
                                    </div>
                                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="position" id="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position') }}" placeholder="Posisi" required>
                                        <label for="position">Posisi</label>
                                    </div>
                                    @error('position')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" placeholder="Tanggal Mulai" required>
                                        <label for="start_date">Tanggal Mulai</label>
                                    </div>
                                    @error('start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" placeholder="Tanggal Selesai" required>
                                        <label for="end_date">Tanggal Selesai</label>
                                    </div>
                                    @error('end_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" placeholder="Deskripsi" style="height: 120px">{{ old('description') }}</textarea>
                                        <label for="description">Deskripsi</label>
                                    </div>
                                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <div class="border rounded p-3 bg-body">
                                        <label class="form-label fw-semibold"><i class="ti ti-users me-2 text-primary"></i>Evaluator yang berpartisipasi</label>
                                        <p class="small text-muted mb-3">Pilih evaluator mana saja yang akan mengevaluasi pelamar pada periode ini. Minimal satu evaluator harus dipilih.</p>
                                        @error('evaluator_ids')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                                        <div class="row g-2">
                                            @forelse($evaluators as $evaluator)
                                                @php
                                                    $checked = collect(old('evaluator_ids', []))->map(fn ($v) => (int) $v)->contains((int) $evaluator->id);
                                                @endphp
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="evaluator_ids[]" value="{{ $evaluator->id }}" id="evaluator_{{ $evaluator->id }}" @checked($checked)>
                                                        <label class="form-check-label" for="evaluator_{{ $evaluator->id }}">
                                                            <strong>{{ $evaluator->code }} — {{ $evaluator->name }}</strong>
                                                            <div class="small text-muted">{{ $evaluator->role_label }}</div>
                                                        </label>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <div class="alert alert-warning mb-0">
                                                        <i class="ti ti-alert-circle me-2"></i>Tidak ada evaluator aktif. Tambahkan evaluator dulu di menu Master Data.
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="border rounded p-3 bg-body">
                                        <p class="small text-muted mb-3">Centang kriteria yang dipakai pada periode ini. Isi <strong>bobot relatif</strong> (&gt; 0); sistem menormalisasi sehingga jumlah bobot = 1 untuk TOPSIS/AHP dasar.</p>
                                        @error('criteria_ids')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                                        @error('weights')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                                        <div class="table-responsive mb-3">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:3rem" class="text-center">Pakai</th>
                                                        <th>Kriteria</th>
                                                        <th style="width:9rem">Bobot relatif</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($criteriaMaster as $c)
                                                        @php
                                                            $picked = collect(old('criteria_ids', []))->map(fn ($v) => (int) $v)->contains((int) $c->id);
                                                        @endphp
                                                        <tr>
                                                            <td class="text-center">
                                                                <input type="checkbox" class="form-check-input js-period-criterion" name="criteria_ids[]" value="{{ $c->id }}" id="crit_{{ $c->id }}" @checked($picked)>
                                                            </td>
                                                            <td>
                                                                <label class="mb-0 fw-medium" for="crit_{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</label>
                                                                <div class="small text-muted">{{ $c->type === 'cost' ? 'Biaya' : 'Keuntungan' }}</div>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="any" min="0" name="weights[{{ $c->id }}]" class="form-control form-control-sm @error('weights.'.$c->id) is-invalid @enderror" value="{{ old('weights.'.$c->id, $picked ? '1' : '') }}" placeholder="0" data-criterion-weight="{{ $c->id }}">
                                                                @error('weights.'.$c->id)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <p class="small text-muted mb-0"><i class="ti ti-alert-circle me-1"></i>Setelah periode ada penilaian, mengurangi atau mengganti kriteria akan menghapus matriks AHP/agregasi terkait — harus isi ulang penilaian bila kolom baru ditambahkan.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mt-4 pt-2 border-top">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-2"></i>Simpan
                                </button>
                                <a href="{{ route('periods.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-2"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
