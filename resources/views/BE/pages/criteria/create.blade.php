@extends('BE.layouts.main')
@section('title', 'Tambah Kriteria')
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
                            <li class="breadcrumb-item" aria-current="page">Tambah</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Tambah Kriteria</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><i class="ti ti-plus me-2"></i>Form kriteria baru</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('criteria.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $suggestedCode) }}" placeholder=" " maxlength="10" required>
                                        <label for="code">Kode <span class="text-danger">*</span></label>
                                        @error('code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted d-block mt-1 ms-1">Saran: {{ $suggestedCode }}</small>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder=" " maxlength="255" required>
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
                                            <option value="benefit" @selected(old('type') === 'benefit')>Benefit (semakin tinggi semakin baik)</option>
                                            <option value="cost" @selected(old('type') === 'cost')>Cost (semakin rendah semakin baik)</option>
                                        </select>
                                        <label for="type">Tipe <span class="text-danger">*</span></label>
                                        @error('type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="number" name="importance" id="importance" class="form-control @error('importance') is-invalid @enderror" value="{{ old('importance') }}" placeholder=" " min="1" max="9" required>
                                        <label for="importance">Kepentingan (1–9) <span class="text-danger">*</span></label>
                                        @error('importance')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" placeholder=" " style="height: 120px">{{ old('description') }}</textarea>
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
            </div>
        </div>
    </div>
</div>
@endsection
