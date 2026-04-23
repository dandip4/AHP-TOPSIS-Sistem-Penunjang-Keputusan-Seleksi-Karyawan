@extends('BE.layouts.main')
@section('title', 'Data Kriteria')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item">Master Data</li>
                            <li class="breadcrumb-item" aria-current="page">Data Kriteria</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Data Kriteria</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><i class="ti ti-list-check me-2"></i>Daftar Kriteria</h5>
                        <a href="{{ route('criteria.create') }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i>Tambah Kriteria
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless datatable mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">No</th>
                                        <th>Kode</th>
                                        <th>Nama Kriteria</th>
                                        <th>Tipe</th>
                                        <th>Kepentingan</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($criteria as $c)
                                    <tr>
                                        <td class="ps-4 align-middle">
                                            <div class="avtar avtar-xs bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold text-primary">{{ $loop->iteration }}</div>
                                        </td>
                                        <td class="align-middle"><span class="badge bg-light-primary">{{ $c->code }}</span></td>
                                        <td class="align-top" style="min-width: 220px;">
                                            <div class="fw-medium">{{ $c->name }}</div>
                                            <button type="button" class="btn btn-sm btn-link-primary text-decoration-none p-0 mt-2 d-inline-flex align-items-center gap-1" data-bs-toggle="collapse" data-bs-target="#subcrit-{{ $c->id }}" aria-expanded="false" aria-controls="subcrit-{{ $c->id }}">
                                                <i class="ti ti-layers-subtract"></i>
                                                <span>Sub-kriteria ({{ $c->subCriteria->count() }})</span>
                                                <i class="ti ti-chevron-down small collapse-chevron"></i>
                                            </button>
                                            <div class="collapse mt-3" id="subcrit-{{ $c->id }}">
                                                <div class="rounded-3 p-3 bg-light text-start">
                                                    <h6 class="small text-uppercase text-muted mb-3 fw-semibold">Tambah sub-kriteria</h6>
                                                    <form method="post" action="{{ route('criteria.sub-criteria.store', $c) }}" class="row g-3 mb-4">
                                                        @csrf
                                                        <div class="col-md-4">
                                                            <div class="form-floating">
                                                                <input type="text" name="name" id="sub-name-{{ $c->id }}" class="form-control" placeholder=" " required maxlength="255">
                                                                <label for="sub-name-{{ $c->id }}">Nama</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-floating">
                                                                <input type="number" name="value" id="sub-value-{{ $c->id }}" class="form-control" placeholder=" " min="1" max="10" required>
                                                                <label for="sub-value-{{ $c->id }}">Nilai (1–10)</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating">
                                                                <input type="text" name="description" id="sub-desc-{{ $c->id }}" class="form-control" placeholder="Deskripsi">
                                                                <label for="sub-desc-{{ $c->id }}">Deskripsi</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1 d-flex align-items-end">
                                                            <button type="submit" class="btn btn-sm btn-primary w-100" title="Simpan">
                                                                <i class="ti ti-plus"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                    <h6 class="small text-uppercase text-muted mb-3 fw-semibold">Daftar sub-kriteria</h6>
                                                    @if($c->subCriteria->isEmpty())
                                                        <div class="text-center text-muted py-4">
                                                            <i class="ti ti-list-details d-block mb-2 fs-2 opacity-50"></i>
                                                            <p class="small mb-0">Belum ada sub-kriteria.</p>
                                                        </div>
                                                    @else
                                                        <ul class="list-group list-group-flush small mb-0">
                                                            @foreach($c->subCriteria as $sub)
                                                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-start gap-2 bg-transparent border-0 border-bottom">
                                                                <div>
                                                                    <span class="fw-medium">{{ $sub->name }}</span>
                                                                    <span class="badge bg-light-primary ms-1">nilai {{ $sub->value }}</span>
                                                                    @if($sub->description)
                                                                        <div class="text-muted mt-1">{{ $sub->description }}</div>
                                                                    @endif
                                                                </div>
                                                                <button type="button" class="avtar avtar-xs bg-light-danger rounded-circle border-0 text-danger d-inline-flex align-items-center justify-content-center flex-shrink-0" style="cursor:pointer;" title="Hapus" onclick="confirmDeleteSub({{ $sub->id }})">
                                                                    <i class="ti ti-trash"></i>
                                                                </button>
                                                                <form id="delete-sub-form-{{ $sub->id }}" action="{{ route('sub-criteria.destroy', $sub) }}" method="POST" class="d-none">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            @if($c->type === 'benefit')
                                                <span class="badge bg-light-success">Benefit</span>
                                            @else
                                                <span class="badge bg-light-warning">Cost</span>
                                            @endif
                                        </td>
                                        <td class="align-middle"><span class="badge bg-light-primary">{{ $c->importance }}</span></td>
                                        <td class="align-middle">
                                            @if($c->is_active)
                                                <span class="badge bg-light-success">Aktif</span>
                                            @else
                                                <span class="badge bg-light-secondary text-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap pe-4 align-middle">
                                            <a href="{{ route('criteria.edit', $c) }}" class="btn btn-sm btn-link-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('criteria.toggle', $c) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-link-primary" title="{{ $c->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <i class="ti ti-toggle-{{ $c->is_active ? 'right' : 'left' }}"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-link-danger" title="Hapus" onclick="confirmDelete({{ $c->id }})">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $c->id }}" action="{{ route('criteria.destroy', $c) }}" method="POST" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5 ps-4 pe-4">
                                            <i class="ti ti-list-check d-block mb-2 fs-1 opacity-25"></i>
                                            <span>Belum ada data kriteria</span>
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
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Yakin?',
        text: 'Data akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
    });
}

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
