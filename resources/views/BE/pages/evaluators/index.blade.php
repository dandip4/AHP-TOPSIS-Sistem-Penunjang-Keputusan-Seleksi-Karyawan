@extends('BE.layouts.main')
@section('title', 'Data Evaluator')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item" aria-current="page">Evaluator</li>
                </ul>
                <div class="page-header-title">
                    <h2 class="mb-0">Data Evaluator KMKK</h2>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">Daftar evaluator</h5>
                <a href="{{ route('evaluators.create') }}" class="btn btn-primary btn-sm"><i class="ti ti-plus me-1"></i>Tambah</a>
            </div>
            <div class="card-body px-0 py-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless mb-0">
                        <thead class="border-bottom">
                            <tr>
                                <th class="ps-4">Nama</th>
                                <th>Kode</th>
                                <th>Peran</th>
                                <th>Pengguna</th>
                                <th>Urutan</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evaluators as $ev)
                            <tr>
                                <td class="ps-4">{{ $ev->name }}</td>
                                <td><code>{{ $ev->code ?? '—' }}</code></td>
                                <td>{{ $ev->role_label ?? '—' }}</td>
                                <td>{{ $ev->user?->email ?? '—' }}</td>
                                <td>{{ $ev->sort_order }}</td>
                                <td>
                                    @if($ev->is_active)
                                    <span class="badge bg-light-success text-success">Aktif</span>
                                    @else
                                    <span class="badge bg-light-secondary text-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('evaluators.edit', $ev) }}" class="btn btn-sm btn-light-warning"><i class="ti ti-pencil"></i></a>
                                    <form action="{{ route('evaluators.destroy', $ev) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus evaluator ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light-danger"><i class="ti ti-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada evaluator.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($evaluators->hasPages())
            <div class="card-footer">{{ $evaluators->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
