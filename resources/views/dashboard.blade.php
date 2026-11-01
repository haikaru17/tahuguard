<!DOCTYPE html>
<html lang="id" data-theme="dark" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TahuGuard ESP32-C3 Edge AIoT Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-amber-500 selection:text-white">

    <!-- PRELOADER OVERLAY -->
    <div id="preloader" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-950 text-white transition-opacity duration-500">
        <div class="cube-wrapper mb-8">
            <div class="cube">
                <div class="cube-face face-front"></div>
                <div class="cube-face face-back"></div>
                <div class="cube-face face-right"></div>
                <div class="cube-face face-left"></div>
                <div class="cube-face face-top"></div>
                <div class="cube-face face-bottom"></div>
            </div>
        </div>
        <h2 class="text-2xl font-bold tracking-wider text-amber-400 mb-1">TahuGuard Edge AIoT</h2>
        <p class="text-xs text-slate-400 mb-6 tracking-widest uppercase">ESP32-C3 Beta</p>
        
        <div class="w-64 bg-slate-800 rounded-full h-2.5 overflow-hidden p-0.5 border border-slate-700">
            <div id="loader-bar" class="bg-gradient-to-r from-amber-500 to-yellow-300 h-full rounded-full w-0 transition-all duration-300"></div>
        </div>
        <p id="loader-text" class="text-xs text-slate-400 mt-2 font-mono">Memuat Sistem... 0%</p>
    </div>

    <!-- APP WRAPPER -->
    <div class="flex-1 flex flex-col md:flex-row max-w-[1920px] w-full mx-auto">

        <!-- SIDEBAR NAVIGATION -->
        <aside id="sidebar" class="w-full md:w-64 theme-card border-b md:border-b-0 md:border-r theme-border flex flex-col shrink-0 z-20">
            <div class="p-4 border-b theme-border flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-slate-900 font-extrabold shadow-lg shadow-amber-500/20">
                        <i class="fa-solid font-bold fa-cube text-xl"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-base leading-tight">TahuGuard</h1>
                        <p class="text-[11px] text-amber-500 font-medium">ESP32-C3 Micro-Edge</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-3 space-y-1.5 overflow-y-auto" id="nav-menu">
                <button onclick="switchTab('telemetry')" id="nav-telemetry" class="nav-btn active w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 bg-amber-500/10 text-amber-500 border border-amber-500/30">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span data-i18n="nav_telemetry">Telemetri Real-Time</span>
                </button>
                <button onclick="switchTab('kinetics')" id="nav-kinetics" class="nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 text-slate-400 hover:bg-slate-800/50 hover:text-slate-200">
                    <i class="fa-solid fa-flask-vial w-5 text-center"></i>
                    <span data-i18n="nav_kinetics">Model Kinetika & AI</span>
                </button>
                <button onclick="switchTab('history')" id="nav-history" class="nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 text-slate-400 hover:bg-slate-800/50 hover:text-slate-200">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                    <span data-i18n="nav_history">Riwayat Batch Log</span>
                </button>
                <button onclick="switchTab('hardware')" id="nav-hardware" class="nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 text-slate-400 hover:bg-slate-800/50 hover:text-slate-200">
                    <i class="fa-solid fa-microchip w-5 text-center"></i>
                    <span data-i18n="nav_hardware">Hardware ESP32-C3</span>
                </button>
                <button onclick="switchTab('developer')" id="nav-developer" class="nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 text-slate-400 hover:bg-slate-800/50 hover:text-slate-200">
                    <i class="fa-solid fa-user-gear w-5 text-center"></i>
                    <span data-i18n="nav_developer">Tentang Pengembang</span>
                </button>
            </nav>

            <div class="p-3 m-3 rounded-xl border theme-border bg-slate-900/40 text-xs space-y-2">
                <div class="flex items-center justify-between text-slate-400">
                    <span data-i18n="system_status">Status Node</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1 animate-ping"></span> ONLINE
                    </span>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- HEADER TOP BAR -->
            <header class="h-16 theme-card border-b theme-border px-4 md:px-6 flex items-center justify-between sticky top-0 z-10 shadow-sm">
                <div class="flex items-center space-x-3">
                    <h2 id="page-title" class="text-lg font-bold">Telemetri Real-Time</h2>
                    <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-500 border border-amber-500/20" data-i18n="badge_skripsi">
                        Riset UMKM Tahu Sumedang
                    </span>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Language Switcher -->
                    <div class="relative inline-block text-left" id="lang-dropdown-wrapper">
                        <button onclick="toggleLangDropdown()" class="px-3 py-1.5 rounded-xl border theme-border theme-card text-xs font-semibold flex items-center space-x-2 hover:border-amber-500 transition-colors shadow-sm">
                            <span id="current-lang-flag">🇮🇩</span>
                            <span id="current-lang-code" class="uppercase">ID</span>
                            <i class="fa-solid fa-chevron-down text-[10px] opacity-60 ml-1"></i>
                        </button>
                        <div id="lang-menu" class="hidden absolute right-0 mt-2 w-36 rounded-xl theme-card border theme-border shadow-xl z-50 overflow-hidden py-1">
                            <button onclick="setLanguage('id')" class="w-full text-left px-4 py-2 text-xs flex items-center space-x-2.5 hover:bg-amber-500/10 hover:text-amber-500 transition-colors">
                                <span>🇮🇩</span><span>Bahasa Indonesia</span>
                            </button>
                            <button onclick="setLanguage('en')" class="w-full text-left px-4 py-2 text-xs flex items-center space-x-2.5 hover:bg-amber-500/10 hover:text-amber-500 transition-colors">
                                <span>🇬🇧</span><span>English</span>
                            </button>
                        </div>
                    </div>

                    <button id="theme-toggle-btn" onclick="toggleTheme()" class="p-2 rounded-xl border theme-border theme-card text-slate-400 hover:text-amber-400 transition-colors shadow-sm">
                        <i id="theme-icon" class="fa-solid fa-sun text-amber-400 text-base"></i>
                    </button>

                    <button id="audio-toggle-btn" onclick="toggleAudio()" class="p-2 rounded-xl border theme-border theme-card text-slate-400 hover:text-amber-400 transition-colors shadow-sm">
                        <i id="audio-icon" class="fa-solid fa-volume-high text-emerald-400 text-base"></i>
                    </button>
                </div>
            </header>

            <!-- TAB 1: REAL-TIME TELEMETRY -->
            <div id="tab-telemetry" class="tab-content p-4 md:p-6 space-y-6 overflow-y-auto flex-1">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="theme-card border theme-border rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400" data-i18n="visualizer_title">Indikator Aktuator Hardware</span>
                            <span id="actuator-status-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                {{ strtoupper($latest->status ?? 'NORMAL') }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-around py-4">
                            <div class="flex flex-col items-center">
                                <div id="led-yellow" class="w-12 h-12 rounded-full border-2 border-yellow-500/40 bg-yellow-500/20 flex items-center justify-center">
                                    <i class="fa-solid fa-hourglass-half text-yellow-400 text-lg"></i>
                                </div>
                                <span class="text-[11px] font-medium mt-2 text-slate-400" data-i18n="status_fermenting">Fermentasi</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div id="led-green" class="w-12 h-12 rounded-full border-2 border-emerald-500/40 bg-emerald-500/20 flex items-center justify-center">
                                    <i class="fa-solid fa-check text-emerald-400 text-lg"></i>
                                </div>
                                <span class="text-[11px] font-medium mt-2 text-slate-400" data-i18n="status_optimal">pH Optimal</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div id="led-red" class="w-12 h-12 rounded-full border-2 border-red-500/40 bg-red-500/20 flex items-center justify-center">
                                    <i class="fa-solid fa-triangle-exclamation text-red-400 text-lg"></i>
                                </div>
                                <span class="text-[11px] font-medium mt-2 text-slate-400" data-i18n="status_over">Terlalu Asam</span>
                            </div>
                        </div>

                        <div class="mt-2 pt-3 border-t theme-border flex items-center justify-between text-xs text-slate-400">
                            <span>Buzzer Web Audio Synth:</span>
                            <span id="buzzer-state-text" class="font-mono text-emerald-400 font-semibold">Aktif (Auto-Tone)</span>
                        </div>
                    </div>
                </div>

                <!-- 5 Metric Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="theme-card border theme-border rounded-2xl p-4 shadow-sm">
                        <span class="text-xs font-semibold uppercase text-slate-400" data-i18n="metric_ph_title">pH Soft Sensor</span>
                        <h3 id="metric-ph" class="text-3xl font-extrabold font-mono text-amber-400 mt-3">{{ $latest->ph_level ?? '6.80' }}</h3>
                    </div>
                    <div class="theme-card border theme-border rounded-2xl p-4 shadow-sm">
                        <span class="text-xs font-semibold uppercase text-slate-400" data-i18n="metric_temp_title">Suhu (NTC 10K)</span>
                        <h3 id="metric-temp" class="text-3xl font-extrabold font-mono text-cyan-400 mt-3">{{ $latest->temperature ?? '32.0' }} °C</h3>
                    </div>
                    <div class="theme-card border theme-border rounded-2xl p-4 shadow-sm">
                        <span class="text-xs font-semibold uppercase text-slate-400">Kelembaban</span>
                        <h3 class="text-3xl font-extrabold font-mono text-sky-400 mt-3">{{ $latest->humidity ?? '75.0' }} %</h3>
                    </div>
                    <div class="theme-card border theme-border rounded-2xl p-4 shadow-sm">
                        <span class="text-xs font-semibold uppercase text-slate-400">Device Node</span>
                        <h3 class="text-xl font-bold font-mono text-emerald-400 mt-3">{{ $latest->device_id ?? 'ESP32C3-01' }}</h3>
                    </div>
                    <div class="theme-card border theme-border rounded-2xl p-4 shadow-sm">
                        <span class="text-xs font-semibold uppercase text-slate-400">Status Node</span>
                        <h3 class="text-xl font-bold font-mono text-purple-400 mt-3">ONLINE</h3>
                    </div>
                </div>

                <!-- Real-Time Chart Section -->
                <div class="theme-card border theme-border rounded-2xl p-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-base" data-i18n="chart_title">Kurva Kinetika pH(t) & Suhu Real-Time</h3>
                            <p class="text-xs text-slate-400" data-i18n="chart_subtitle">Rentang Optimal Penggumpalan Tahu Sumedang Ditandai Warna Hijau (pH 4.2 - 5.2)</p>
                        </div>
                    </div>
                    <div class="w-full h-80 relative">
                        <canvas id="telemetryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- TAB 2: KINETICS & AI MODEL -->
            <div id="tab-kinetics" class="tab-content hidden p-4 md:p-6 space-y-6 overflow-y-auto flex-1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="theme-card border theme-border rounded-2xl p-6 shadow-sm space-y-4">
                        <h3 class="font-bold text-lg text-amber-500" data-i18n="kinetics_math_title">Persamaan Kinetika Penggumpalan</h3>
                        <p class="text-xs text-slate-400">Soft sensor ini menggantikan sensor pH kaca yang mudah rusak di asam whey panas dengan model kinetika orde pertama yang dipengaruhi suhu NTC.</p>
                        <div class="bg-slate-900 p-4 rounded-xl font-mono text-xs text-amber-300">
                            <p>k(T) = 0.25 + 0.015 × (T - 30.0)</p>
                            <p>pH(t) = 3.5 + 3.3 × e^(-k × t)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: BATCH HISTORY LOG -->
            <div id="tab-history" class="tab-content hidden p-4 md:p-6 space-y-6 overflow-y-auto flex-1">
                <div class="theme-card border theme-border rounded-2xl p-5 shadow-sm space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="font-bold text-base" data-i18n="history_title">Riwayat Log Batch Produksi Tahu</h3>
                        <a href="{{ route('sensor.export') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 text-white">Ekspor Data CSV</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-900/60 text-slate-400 uppercase">
                                <tr>
                                    <th class="p-3">Waktu Record</th>
                                    <th class="p-3">Device ID</th>
                                    <th class="p-3">Suhu (°C)</th>
                                    <th class="p-3">Kelembaban (%)</th>
                                    <th class="p-3">pH Level</th>
                                    <th class="p-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y theme-border font-mono">
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="p-3 text-slate-400">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td class="p-3 font-semibold">{{ $log->device_id }}</td>
                                        <td class="p-3 text-emerald-400">{{ $log->temperature }} °C</td>
                                        <td class="p-3 text-sky-400">{{ $log->humidity }} %</td>
                                        <td class="p-3 text-purple-400">{{ $log->ph_level ?? '-' }}</td>
                                        <td class="p-3 font-bold uppercase {{ $log->status === 'warning' ? 'text-amber-400' : 'text-emerald-400' }}">{{ $log->status }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="p-4 text-center text-slate-500">Belum ada data sensor.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4: ESP32-C3 HARDWARE SPECS -->
            <div id="tab-hardware" class="tab-content hidden p-4 md:p-6 space-y-6 overflow-y-auto flex-1">
                <div class="theme-card border theme-border rounded-2xl p-5 shadow-sm space-y-3">
                    <h4 class="font-bold text-sm">ESP32-C3 RISC-V SoC Specs</h4>
                    <p class="text-xs text-slate-400">Single-core 32-bit RISC-V @ 160MHz, 400KB SRAM, 4MB Flash.</p>
                </div>
            </div>

            <!-- TAB 5: ABOUT DEVELOPER -->
            <div id="tab-developer" class="tab-content hidden p-4 md:p-6 space-y-6 overflow-y-auto flex-1">
                <div class="theme-card border theme-border rounded-3xl p-6 shadow-md">
                    <h3 class="text-xl font-extrabold text-white mb-4">Tim Pengembang TahuGuard</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                            <span class="font-bold text-amber-400 block">Haikal Luthfianino Balukia</span>
                            <span class="text-slate-400">Embedded Systems Specialist (haikalluthfianinobalukia@proton.me)</span>
                        </div>
                        <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                            <span class="font-bold text-emerald-400 block">Muzayin Jamil</span>
                            <span class="text-slate-400">Backend Lead Engineer (zeinofficial19@gmail.com)</span>
                        </div>
                        <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                            <span class="font-bold text-sky-400 block">Raidan Arya Pradana</span>
                            <span class="text-slate-400">IoT Hardware & Actuators (aryapradanaraidan@gmail.com)</span>
                        </div>
                        <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                            <span class="font-bold text-purple-400 block">Piere Anggadipraja Sukandi</span>
                            <span class="text-slate-400">UI/UX & Frontend Engineer (wetansumedang91@gmail.com)</span>
                        </div>
                        <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800 sm:col-span-2">
                            <span class="font-bold text-rose-400 block">Dani Sonjaya</span>
                            <span class="text-slate-400">Network & Telemetry Analyst (danisonjaya73@gmail.com)</span>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>