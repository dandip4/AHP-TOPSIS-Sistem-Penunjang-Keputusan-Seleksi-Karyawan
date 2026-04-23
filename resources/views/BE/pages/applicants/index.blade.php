@extends('BE.layouts.main')
@section('title', 'Data Pelamar')
@section('container')
<div class="pc-container"><div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page">Data Pelamar</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Data Pelamar</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="flex-grow-1 flex-md-grow-0 rounded-3 border bg-body p-3" style="min-width: min(100%, 320px);">
                            <form method="get" action="{{ route('applicants.index') }}" class="d-flex align-items-center gap-3">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-light-primary flex-shrink-0 wid-45 hei-45"><i class="ti ti-filter f-18"></i></span>
                                <div class="form-floating flex-grow-1 mb-0">
                                    <select name="period_id" id="period_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua periode</option>
                                        @foreach($periods as $period)
                                            <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>{{ $period->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="period_id">Filter periode</label>
                                </div>
                            </form>
                        </div>
                        <a href="{{ route('applicants.create') }}" class="btn btn-primary btn-sm flex-shrink-0">
                            <i class="ti ti-plus me-1"></i>Tambah Pelamar
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive ps-4 pe-4">
                            <table class="table table-hover table-borderless datatable mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-muted fw-normal">No</th>
                                        <th class="text-muted fw-normal">Nama</th>
                                        <th class="text-muted fw-normal">Email</th>
                                        <th class="text-muted fw-normal">Telepon</th>
                                        <th class="text-muted fw-normal">Gender</th>
                                        <th class="text-muted fw-normal">Pendidikan</th>
                                        <th class="text-muted fw-normal">Usia</th>
                                        <th class="text-muted fw-normal">Periode</th>
                                        <th class="text-muted fw-normal text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applicants as $applicant)
                                    <tr>
                                        <td class="align-middle text-muted">{{ $loop->iteration }}</td>
                                        <td class="align-middle">
                                            <h6 class="mb-0 fw-semibold">{{ $applicant->name }}</h6>
                                        </td>
                                        <td class="align-middle">{{ $applicant->email }}</td>
                                        <td class="align-middle">{{ $applicant->phone }}</td>
                                        <td class="align-middle">
                                            @if($applicant->gender === 'L')
                                                <span class="badge bg-light-primary">L</span>
                                            @elseif($applicant->gender === 'P')
                                                <span class="badge bg-light-danger">P</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">{{ $applicant->education }}</td>
                                        <td class="align-middle">{{ $applicant->age }}</td>
                                        <td class="align-middle">{{ $applicant->period->name ?? '—' }}</td>
                                        <td class="text-end text-nowrap align-middle">
                                            <a href="{{ route('applicants.edit', $applicant) }}" class="btn btn-sm btn-light-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-light-danger" title="Hapus" onclick="confirmDelete({{ $applicant->id }})">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $applicant->id }}" action="{{ route('applicants.destroy', $applicant) }}" method="post" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center gap-2 text-muted">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light-secondary wid-60 hei-60"><i class="ti ti-users f-24"></i></span>
                                                <p class="mb-0 fw-medium">Belum ada data pelamar</p>
                                                <p class="small mb-0">Tambah pelamar atau ubah filter periode.</p>
                                            </div>
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
</div></div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Yakin?',
        text: 'Data pelamar akan dihapus!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
    });
}
</script>
@endpush
