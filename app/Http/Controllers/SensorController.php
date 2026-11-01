<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorData;
use Illuminate\Support\Facades\Http;

class SensorController extends Controller
{
    public function index()
    {
        $latest = SensorData::latest()->first();
        $logs = SensorData::latest()->take(10)->get();
        return view('dashboard', compact('latest', 'logs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'   => 'required|string',
            'temperature' => 'required|numeric',
            'humidity'    => 'required|numeric',
            'ph_level'    => 'nullable|numeric',
        ]);

        // Evaluasi kondisi pH asam / basi
        $validated['status'] = ($validated['ph_level'] ?? 7.0) < 4.2 ? 'warning' : 'normal';

        $log = SensorData::create($validated);

        // Teruskan data telemetri ke Node.js Socket.io Microservice
        try {
            Http::timeout(1)->post('http://127.0.0.1:6001/emit-sensor', [
                'device_id'   => $log->device_id,
                'temperature' => $log->temperature,
                'humidity'    => $log->humidity,
                'ph_level'    => $log->ph_level,
                'status'      => $log->status,
                'created_at'  => $log->created_at->format('H:i:s'),
            ]);
        } catch (\Exception $e) {}

        // Telegram Notification jika status Warning
        if ($log->status === 'warning') {
            $this->sendTelegramAlert($log);
        }

        return response()->json(['success' => true, 'data' => $log]);
    }

    public function getChartData()
    {
        $logs = SensorData::latest()->take(20)->get()->reverse()->values();
        return response()->json(['success' => true, 'data' => $logs]);
    }

    public function exportCSV()
    {
        $logs = SensorData::all();
        $csvFileName = 'tahuguard_telemetry_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Device ID', 'Suhu (C)', 'Kelembaban (%)', 'pH Level', 'Status', 'Waktu']);
            foreach ($logs as $row) {
                fputcsv($handle, [$row->id, $row->device_id, $row->temperature, $row->humidity, $row->ph_level, $row->status, $row->created_at]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function sendTelegramAlert($log)
{
    $token = env('TELEGRAM_BOT_TOKEN');
    $chatId = env('TELEGRAM_CHAT_ID');

    if ($token && $chatId) {
        try {
            // Tambahkan ->withoutVerifying() untuk mengabaikan rantai sertifikat SSL di environment lokal
            Http::withoutVerifying()->timeout(3)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => "⚠️ *PERINGATAN TAHUGUARD*\n\nDevice: {$log->device_id}\nSuhu: {$log->temperature} °C\npH Level: {$log->ph_level}\nStatus: *CRITICAL (ASAM/BASI)*",
                'parse_mode' => 'Markdown'
            ]);
        } catch (\Exception $e) {
            \Log::warning("Gagal mengirim Telegram Alert: " . $e->getMessage());
        }
    }
}
}