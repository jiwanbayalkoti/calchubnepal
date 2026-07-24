<?php

use App\Http\Controllers\Api\V1\CalculatorController;
use App\Http\Controllers\Api\V1\QrCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('calculators', [CalculatorController::class, 'index']);
    Route::get('calculators/{slug}', [CalculatorController::class, 'show']);
    Route::post('calculators/{slug}/calculate', [CalculatorController::class, 'calculate'])
        ->middleware('throttle:60,1');

    Route::middleware(['api.key', 'throttle:60,1'])->group(function () {
        Route::get('qr-codes', [QrCodeController::class, 'index']);
        Route::post('qr-codes', [QrCodeController::class, 'store']);
        Route::get('qr-codes/{qrCode}', [QrCodeController::class, 'show']);
        Route::put('qr-codes/{qrCode}', [QrCodeController::class, 'update']);
        Route::delete('qr-codes/{qrCode}', [QrCodeController::class, 'destroy']);
        Route::get('qr-codes/{qrCode}/analytics', [QrCodeController::class, 'analytics']);
        Route::get('qr-codes/{qrCode}/scans', [QrCodeController::class, 'scans']);
    });
});
