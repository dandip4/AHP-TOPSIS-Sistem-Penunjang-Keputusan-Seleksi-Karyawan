@extends('BE.layouts.main')
@section('title', 'Tambah Evaluator')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('evaluators.index') }}">Evaluator</a></li>
                    <li class="breadcrumb-item" aria-current="page">Tambah</li>
                </ul>
                <div class="page-header-title"><h2 class="mb-0">Tambah Evaluator</h2></div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('evaluators.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kode (opsional)</label>
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control" maxlength="48">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Label peran</label>
                        <input type="text" name="role_label" value="{{ old('role_label') }}" class="form-control" placeholder="Mis. HRD" maxlength="96">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Akun pengguna (opsional)</label>
                        <select name="user_id" class="form-select">
                            <option value="">— Tidak ada —</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex align-items-center pt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', true))>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                        <a href="{{ route('evaluators.index') }}" class="btn btn-link">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
