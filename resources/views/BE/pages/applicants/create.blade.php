@extends('BE.layouts.main')
@section('title', 'Tambah Pelamar')
@section('container')
<div class="pc-container"><div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('applicants.index') }}">Data Pelamar</a></li>
                            <li class="breadcrumb-item" aria-current="page">Tambah Pelamar</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Tambah Pelamar</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Formulir pelamar</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('applicants.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <select name="period_id" id="period_id" class="form-select @error('period_id') is-invalid @enderror" required>
                                            <option value="">Pilih periode</option>
                                            @foreach($periods as $period)
                                                <option value="{{ $period->id }}" @selected(old('period_id') == $period->id)>{{ $period->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="period_id">Periode <span class="text-danger">*</span></label>
                                    </div>
                                    @error('period_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Nama" required>
                                        <label for="name">Nama <span class="text-danger">*</span></label>
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Email" required>
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="Telepon" required>
                                        <label for="phone">Telepon <span class="text-danger">*</span></label>
                                    </div>
                                    @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                            <option value="">Pilih gender</option>
                                            <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                                            <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                                        </select>
                                        <label for="gender">Gender <span class="text-danger">*</span></label>
                                    </div>
                                    @error('gender')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" name="birth_date" id="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date') }}" placeholder="Tanggal lahir" required>
                                        <label for="birth_date">Tanggal Lahir <span class="text-danger">*</span></label>
                                    </div>
                                    @error('birth_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select name="education" id="education" class="form-select @error('education') is-invalid @enderror" required>
                                            <option value="">Pilih pendidikan</option>
                                            @foreach(['SMA/SMK', 'D3', 'S1', 'S2', 'S3'] as $edu)
                                                <option value="{{ $edu }}" @selected(old('education') === $edu)>{{ $edu }}</option>
                                            @endforeach
                                        </select>
                                        <label for="education">Pendidikan <span class="text-danger">*</span></label>
                                    </div>
                                    @error('education')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" name="major" id="major" class="form-control @error('major') is-invalid @enderror" value="{{ old('major') }}" placeholder="Jurusan" required>
                                        <label for="major">Jurusan <span class="text-danger">*</span></label>
                                    </div>
                                    @error('major')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" name="gpa" id="gpa" class="form-control @error('gpa') is-invalid @enderror" value="{{ old('gpa') }}" step="0.01" min="0" max="4" placeholder="IPK" required>
                                        <label for="gpa">IPK <span class="text-danger">*</span></label>
                                    </div>
                                    @error('gpa')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" name="age" id="age" class="form-control @error('age') is-invalid @enderror" value="{{ old('age') }}" min="17" max="60" placeholder="Usia" required>
                                        <label for="age">Usia <span class="text-danger">*</span></label>
                                    </div>
                                    @error('age')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" style="height: 100px" placeholder="Alamat" required>{{ old('address') }}</textarea>
                                        <label for="address">Alamat <span class="text-danger">*</span></label>
                                    </div>
                                    @error('address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i>Simpan
                                </button>
                                <a href="{{ route('applicants.index') }}" class="btn btn-light-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
</div></div>
@endsection
