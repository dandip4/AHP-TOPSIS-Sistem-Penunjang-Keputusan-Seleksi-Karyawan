<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalculationController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EvaluatorController;
use App\Http\Controllers\KmkkGroupResultController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SelectionPeriodController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
    Route::post('evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');

    Route::get('kmkk', [KmkkGroupResultController::class, 'index'])->name('kmkk.group-results');
    Route::post('kmkk/rebuild', [KmkkGroupResultController::class, 'rebuild'])
        ->middleware('role:admin')
        ->name('kmkk.rebuild');

    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('trend', [AnalyticsController::class, 'trend'])->name('trend');
        Route::get('prediction', [AnalyticsController::class, 'prediction'])->name('prediction');
        Route::get('sensitivity', [AnalyticsController::class, 'sensitivity'])->name('sensitivity');
        Route::get('quality-control', [AnalyticsController::class, 'qualityControl'])->name('quality-control');

        // API endpoints
        Route::get('api/chart-score-distribution', [AnalyticsController::class, 'chartScoreDistribution'])->name('api.chart-score-distribution');
        Route::get('api/chart-evaluator-performance', [AnalyticsController::class, 'chartEvaluatorPerformance'])->name('api.chart-evaluator-performance');
        Route::get('api/chart-trend', [AnalyticsController::class, 'chartTrend'])->name('api.chart-trend');
    });

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{period}/print', [ReportController::class, 'print'])->name('reports.print');

    Route::middleware('role:admin')->group(function () {
        Route::resource('criteria', CriteriaController::class)->except('show');
        Route::post('criteria/{criteria}/toggle', [CriteriaController::class, 'toggleActive'])->name('criteria.toggle');
        Route::post('criteria/{criteria}/sub-criteria', [CriteriaController::class, 'storeSubCriteria'])->name('criteria.sub-criteria.store');
        Route::delete('sub-criteria/{subCriteria}', [CriteriaController::class, 'destroySubCriteria'])->name('sub-criteria.destroy');

        Route::resource('periods', SelectionPeriodController::class);
        Route::resource('applicants', ApplicantController::class)->except('show');

        Route::prefix('calculations')->name('calculations.')->group(function () {
            Route::get('ahp', [CalculationController::class, 'ahp'])->name('ahp');
            Route::post('ahp', [CalculationController::class, 'calculateAhp'])->name('ahp.calculate');
            Route::get('topsis', [CalculationController::class, 'topsis'])->name('topsis');
            Route::post('topsis', [CalculationController::class, 'calculateTopsis'])->name('topsis.calculate');
            Route::get('results', [CalculationController::class, 'results'])->name('results');
        });

        Route::resource('announcements', AnnouncementController::class)->except('show');
        Route::resource('evaluators', EvaluatorController::class)->except('show');
    });
});
