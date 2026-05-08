@extends('BE.layouts.main')
@section('title', 'Pengumuman')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page">Pengumuman</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h2 class="mb-0"><i class="ti ti-speakerphone me-2 text-primary"></i>Pengumuman</h2>
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i>Tambah pengumuman
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">Daftar pengumuman</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless mb-0 align-middle">
                                <thead class="border-bottom">
                                    <tr>
                                        <th class="ps-4" style="width:3.5rem;">No</th>
                                        <th>Judul &amp; periode</th>
                                        <th style="width:8rem;">Status</th>
                                        <th style="width:11rem;">Tanggal</th>
                                        @if(Auth::user()->isAdmin())
                                        <th class="text-end pe-4" style="width:7rem;">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($announcements as $announcement)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <h6 class="mb-1 fw-semibold">{{ $announcement->title }}</h6>
                                            <span class="text-muted small"><i class="ti ti-calendar-event me-1"></i>{{ $announcement->period?->name ?? '—' }}</span>
                                        </td>
                                        <td>
                                            @if($announcement->is_published)
                                            <span class="badge bg-success">Published</span>
                                            @else
                                            <span class="badge bg-secondary">Draft</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small text-nowrap">
                                                @if($announcement->is_published && $announcement->published_at)
                                                {{ $announcement->published_at->translatedFormat('d M Y, H:i') }}
                                                @else
                                                {{ $announcement->created_at->translatedFormat('d M Y, H:i') }}
                                                @endif
                                            </span>
                                        </td>
                                        @if(Auth::user()->isAdmin())
                                        <td class="text-end pe-4 text-nowrap">
                                            <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-sm btn-light-primary btn-icon" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-light-danger btn-icon" title="Hapus" onclick="confirmDelete({{ $announcement->id }})">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $announcement->id }}" action="{{ route('announcements.destroy', $announcement) }}" method="post" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->isAdmin() ? 5 : 4 }}" class="p-0">
                                            <div class="text-center py-5 px-4">
                                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light-secondary text-secondary mb-3" style="width:3.5rem;height:3.5rem;">
                                                    <i class="ti ti-inbox f-24"></i>
                                                </div>
                                                <p class="text-muted mb-0">Belum ada pengumuman. Tambahkan dari tombol di atas jika Anda admin.</p>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Yakin?', text: 'Data akan dihapus!', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
    });
}
</script>
@endpush
