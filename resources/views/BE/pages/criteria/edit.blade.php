@extends('BE.layouts.main')
@section('title', 'Edit Kriteria')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('criteria.index') }}">Data Kriteria</a></li>
                            <li class="breadcrumb-item" aria-current="page">Edit</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Edit Kriteria</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><i class="ti ti-edit me-2"></i>Data kriteria</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('criteria.update', $criteria) }}">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $criteria->code) }}" placeholder=" " maxlength="10" required>
                                        <label for="code">Kode <span class="text-danger">*</span></label>
                                        @error('code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $criteria->name) }}" placeholder=" " maxlength="255" required>
                                        <label for="name">Nama Kriteria <span class="text-danger">*</span></label>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                            <option value="">Pilih tipe</option>
                                            <option value="benefit" @selected(old('type', $criteria->type) === 'benefit')>Benefit (semakin tinggi semakin baik)</option>
                                            <option value="cost" @selected(old('type', $criteria->type) === 'cost')>Cost (semakin rendah semakin baik)</option>
                                        </select>
                                        <label for="type">Tipe <span class="text-danger">*</span></label>
                                        @error('type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="number" name="importance" id="importance" class="form-control @error('importance') is-invalid @enderror" value="{{ old('importance', $criteria->importance) }}" placeholder=" " min="1" max="9" required>
                                        <label for="importance">Kepentingan (1–9) <span class="text-danger">*</span></label>
                                        @error('importance')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" placeholder=" " style="height: 120px">{{ old('description', $criteria->description) }}</textarea>
                                        <label for="description">Deskripsi</label>
                                        @error('description')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted d-block mt-1 ms-1">Opsional</small>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i>Simpan
                                </button>
                                <a href="{{ route('criteria.index') }}" class="btn btn-light-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><i class="ti ti-layers-subtract me-2"></i>Sub-kriteria</h5>
                    </div>
                    <div class="card-body">
                        <div class="rounded-3 p-3 bg-light mb-4">
                            <h6 class="small text-uppercase text-muted mb-3 fw-semibold">Tambah sub-kriteria</h6>
                            <form method="post" action="{{ route('criteria.sub-criteria.store', $criteria) }}" class="row g-3">
                                @csrf
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" name="name" id="sub-name-edit" class="form-control" placeholder=" " required maxlength="255">
                                        <label for="sub-name-edit">Nama <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating">
                                        <input type="number" name="value" id="sub-value-edit" class="form-control" placeholder=" " min="1" max="10" required>
                                        <label for="sub-value-edit">Nilai (1–10) <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating">
                                        <input type="text" name="description" id="sub-desc-edit" class="form-control" placeholder=" ">
                                        <label for="sub-desc-edit">Deskripsi</label>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti ti-plus me-1"></i>Tambah
                                    </button>
                                </div>
                            </form>
                        </div>

                        <h6 class="small text-uppercase text-muted mb-3 fw-semibold">Daftar sub-kriteria</h6>
                        <div class="card border-0 shadow-none bg-transparent p-0">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless datatable mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">No</th>
                                                <th>Nama</th>
                                                <th>Nilai</th>
                                                <th>Deskripsi</th>
                                                <th class="text-end pe-4">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($criteria->subCriteria as $sub)
                                            <tr>
                                                <td class="ps-4 align-middle">
                                                    <div class="avtar avtar-xs bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold text-primary">{{ $loop->iteration }}</div>
                                                </td>
                                                <td class="align-middle">{{ $sub->name }}</td>
                                                <td class="align-middle"><span class="badge bg-light-primary">{{ $sub->value }}</span></td>
                                                <td class="align-middle">{{ $sub->description ?? '—' }}</td>
                                                <td class="text-end pe-4 align-middle">
                                                    <button type="button" class="btn btn-sm btn-link-danger" title="Hapus" onclick="confirmDeleteSub({{ $sub->id }})">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                    <form id="delete-sub-form-{{ $sub->id }}" action="{{ route('sub-criteria.destroy', $sub) }}" method="POST" class="d-none">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5 ps-4 pe-4">
                                                    <i class="ti ti-list-details d-block mb-2 fs-1 opacity-25"></i>
                                                    <span>Belum ada sub-kriteria</span>
                                                </td>
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

@push('scripts')
<script>
function confirmDeleteSub(id) {
    Swal.fire({
        title: 'Yakin?',
        text: 'Sub-kriteria akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('delete-sub-form-' + id).submit();
    });
}
</script>
@endpush
