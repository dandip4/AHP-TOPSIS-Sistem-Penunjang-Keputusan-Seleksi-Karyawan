@extends('BE.layouts.main')
@section('title', 'Trend Analysis')
@section('container')
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('analytics.index') }}">Analytics</a></li>
                            <li class="breadcrumb-item" aria-current="page">Trend Analysis</li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Trend Analysis ({{ $lookbackPeriods }} Periode Terakhir)</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @if (!empty($trendData['pass_rate_trend']))
        <!-- Pass Rate Trend -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Trend Persentase Kelulusan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Periode</th>
                                        <th>Total Pelamar</th>
                                        <th>Lulus</th>
                                        <th>Tidak Lulus</th>
                                        <th>% Lulus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($trendData['pass_rate_trend'] as $item)
                                        <tr>
                                            <td><strong>{{ $item['period_name'] }}</strong></td>
                                            <td>{{ $item['total_applicants'] }}</td>
                                            <td><span class="badge badge-light text-dark">{{ $item['lulus_count'] }}</span></td>
                                            <td><span class="badge badge-light text-dark">{{ $item['total_applicants'] - $item['lulus_count'] }}</span></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                         style="width: {{ $item['pass_rate_percentage'] }}%"
                                                         aria-valuenow="{{ $item['pass_rate_percentage'] }}"
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        {{ $item['pass_rate_percentage'] }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (!empty($trendData['weight_trend']))
        <!-- Weight Trend -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Trend Bobot Kriteria</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($trendData['weight_trend'] as $criteria_code => $weights)
                            <div class="mb-4">
                                <h6>{{ $criteria_code }} - Bobot per Periode</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            @foreach ($weights as $w)
                                                <td style="text-align: center;">
                                                    <strong>{{ $w['period_name'] }}</strong><br>
                                                    <span class="badge badge-light text-dark">{{ $w['weight'] }}</span>
                                                </td>
                                            @endforeach
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Tidak ada data trend untuk ditampilkan.
        </div>
    @endif

    <!-- Navigation -->
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Analytics</a>
        </div>
    </div>
    </div>
</div>
@endsection
