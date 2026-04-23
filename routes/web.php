<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalculationController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SelectionPeriodController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('criteria', CriteriaController::class)->except('show');
    Route::post('criteria/{criteria}/toggle', [CriteriaController::class, 'toggleActive'])->name('criteria.toggle');
    Route::post('criteria/{criteria}/sub-criteria', [CriteriaController::class, 'storeSubCriteria'])->name('criteria.sub-criteria.store');
    Route::delete('sub-criteria/{subCriteria}', [CriteriaController::class, 'destroySubCriteria'])->name('sub-criteria.destroy');

    Route::resource('periods', SelectionPeriodController::class);
    Route::resource('applicants', ApplicantController::class)->except('show');

    Route::get('evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
    Route::post('evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');

    Route::prefix('calculations')->name('calculations.')->group(function () {
        Route::get('ahp', [CalculationController::class, 'ahp'])->name('ahp');
        Route::post('ahp', [CalculationController::class, 'calculateAhp'])->name('ahp.calculate');
        Route::get('topsis', [CalculationController::class, 'topsis'])->name('topsis');
        Route::post('topsis', [CalculationController::class, 'calculateTopsis'])->name('topsis.calculate');
        Route::get('results', [CalculationController::class, 'results'])->name('results');
    });

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{period}/print', [ReportController::class, 'print'])->name('reports.print');

    Route::resource('announcements', AnnouncementController::class)->except('show');
});
