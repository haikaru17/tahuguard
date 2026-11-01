<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

Route::get('/', [SensorController::class, 'index'])->name('dashboard');
Route::post('/api/sensor', [SensorController::class, 'store']);
Route::get('/api/sensor/chart', [SensorController::class, 'getChartData']);
Route::get('/sensor/export', [SensorController::class, 'exportCSV'])->name('sensor.export');