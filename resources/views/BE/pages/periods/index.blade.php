@extends('BE.layouts.main')
@section('title', 'Periode Seleksi')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page">Periode Seleksi</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Periode Seleksi</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0"><i class="ti ti-calendar-event me-2"></i>Daftar Periode</h5>
                        <a href="{{ route('periods.create') }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i>Tambah Periode
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless datatable mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 text-muted fw-normal">No</th>
                                        <th class="text-muted fw-normal">Periode</th>
                                        <th class="text-muted fw-normal">Posisi</th>
                                        <th class="text-muted fw-normal">Jadwal</th>
                                        <th class="text-muted fw-normal">Pelamar</th>
                                        <th class="text-muted fw-normal">Status</th>
                                        <th class="pe-4 text-end text-muted fw-normal">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($periods as $period)
                                    <tr>
                                        <td class="ps-4 align-middle">
                                            <div class="avtar avtar-xs bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold text-primary">{{ $loop->iteration }}</div>
                                        </td>
                                        <td class="align-middle" style="min-width: 200px;">
                                            <h6 class="mb-0 fw-semibold">{{ $period->name }}</h6>
                                            <small class="text-muted">Oleh {{ $period->creator?->name ?? '—' }}</small>
                                        </td>
                                        <td class="align-middle">{{ $period->position }}</td>
                                        <td class="align-middle text-nowrap">
                                            <div class="d-flex align-items-center gap-1">
                                                <i class="ti ti-calendar-event text-muted"></i>
                                                <span>{{ $period->start_date->locale('id')->translatedFormat('d MMM Y') }}</span>
                                            </div>
                                            <small class="text-muted d-block ms-4">s/d {{ $period->end_date->locale('id')->translatedFormat('d MMM Y') }}</small>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-light-primary">{{ $period->applicants_count }} pelamar</span>
                                        </td>
                                        <td class="align-middle">
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
                                        </td>
                                        <td class="pe-4 text-end align-middle text-nowrap">
                                            <a href="{{ route('periods.show', $period) }}" class="btn btn-sm btn-light-primary" title="Detail">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <a href="{{ route('periods.edit', $period) }}" class="btn btn-sm btn-light-warning" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-light-danger" title="Hapus" onclick="confirmDelete({{ $period->id }})">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $period->id }}" action="{{ route('periods.destroy', $period) }}" method="POST" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5 ps-4 pe-4">Belum ada periode seleksi.</td>
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
        title: 'Yakin?', text: 'Data akan dihapus permanen!', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
    });
}
</script>
@endpush
