<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

Route::get('/', [SensorController::class, 'index'])->name('dashboard');

// Ingestion Telemetri Sensor (Mendukung endpoint utama & rute firmware ESP32)
Route::post('/api/sensor', [SensorController::class, 'store']);
Route::post('/api/sensor/telemetry', [SensorController::class, 'store']);

Route::get('/api/sensor/chart', [SensorController::class, 'getChartData']);
Route::get('/sensor/export', [SensorController::class, 'exportCSV'])->name('sensor.export');