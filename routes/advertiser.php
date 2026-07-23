<?php

use App\Http\Controllers\Advertiser\AdvertisementController as AdvertiserAdController;
use App\Http\Controllers\Advertiser\DashboardController as AdvertiserDashboardController;
use App\Http\Controllers\Advertiser\ProfileController as AdvertiserProfileController;
use App\Http\Controllers\Advertiser\ReportController as AdvertiserReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'advertiser'])->prefix('advertiser')->name('advertiser.')->group(function () {
    Route::get('/dashboard', [AdvertiserDashboardController::class, 'index'])->name('dashboard');

    Route::get('/advertisements', [AdvertiserAdController::class, 'index'])->name('advertisements.index');
    Route::get('/advertisements/data', [AdvertiserAdController::class, 'data'])->name('advertisements.data');
    Route::get('/advertisements/{id}', [AdvertiserAdController::class, 'show'])->name('advertisements.show');

    Route::get('/reports', [AdvertiserReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [AdvertiserReportController::class, 'data'])->name('reports.data');
    Route::get('/reports/export/excel', [AdvertiserReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export/pdf', [AdvertiserReportController::class, 'exportPdf'])->name('reports.export.pdf');

    Route::get('/profile', [AdvertiserProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [AdvertiserProfileController::class, 'update'])->name('profile.update');
});
