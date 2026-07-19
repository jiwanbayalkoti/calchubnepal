<?php

use App\Http\Controllers\Api\V1\CalculatorController;
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
});
