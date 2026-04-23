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
