@extends('BE.layouts.main')
@section('title', 'Edit Pengumuman')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">Pengumuman</a></li>
                            <li class="breadcrumb-item" aria-current="page">Edit Pengumuman</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0"><i class="ti ti-speakerphone me-2 text-primary"></i>Edit pengumuman</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">Form pengumuman</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('announcements.update', $announcement) }}">
                            @csrf
                            @method('PUT')
                            <div class="form-floating mb-3">
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $announcement->title) }}" placeholder=" " required autocomplete="off">
                                <label for="title">Judul <span class="text-danger">*</span></label>
                            </div>
                            @error('title')
                            <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
                            @enderror

                            <div class="form-floating mb-3">
                                <select name="period_id" id="period_id" class="form-select @error('period_id') is-invalid @enderror" aria-label="Periode">
                                    <option value="">— Opsional —</option>
                                    @foreach($periods as $period)
                                    <option value="{{ $period->id }}" @selected(old('period_id', $announcement->period_id) == $period->id)>{{ $period->name }}</option>
                                    @endforeach
                                </select>
                                <label for="period_id">Periode</label>
                            </div>
                            @error('period_id')
                            <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
                            @enderror

                            <div class="form-floating mb-3">
                                <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" placeholder=" " style="height: 220px" rows="8" required>{{ old('content', $announcement->content) }}</textarea>
                                <label for="content">Isi <span class="text-danger">*</span></label>
                            </div>
                            @error('content')
                            <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
                            @enderror

                            <div class="form-check form-switch mb-4">
                                <input type="checkbox" name="is_published" id="is_published" value="1" class="form-check-input" role="switch" @checked(old('is_published', $announcement->is_published))>
                                <label class="form-check-label" for="is_published">Terbitkan</label>
                            </div>

                            <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i>Simpan
                                </button>
                                <a href="{{ route('announcements.index') }}" class="btn btn-light-secondary">
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
