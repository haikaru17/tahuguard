// Socket.io Real-Time Ingestion
const socket = typeof io !== 'undefined' ? io('http://localhost:6001') : null;
if (socket) {
    socket.on('telemetry-update', (data) => {
        updateMetricCards(data);
        fetchDashboardData();
    });
}

async function fetchDashboardData() {
    try {
        const res = await fetch('/api/sensor/chart');
        const json = await res.json();
        if (json.success && json.data.length > 0) {
            const labels = json.data.map(item => new Date(item.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
            telemetryChart.data.labels = labels;
            telemetryChart.data.datasets[0].data = json.data.map(item => item.ph_level ?? 6.8);
            telemetryChart.data.datasets[1].data = json.data.map(item => item.temperature);
            telemetryChart.update();

            const latest = json.data[json.data.length - 1];
            updateMetricCards(latest);
        }
    } catch (err) {
        console.error("Error polling IoT telemetry:", err);
    }
}

function updateMetricCards(data) {
    if (document.getElementById('metric-ph')) document.getElementById('metric-ph').innerText = data.ph_level ? parseFloat(data.ph_level).toFixed(2) : '6.80';
    if (document.getElementById('metric-temp')) document.getElementById('metric-temp').innerText = parseFloat(data.temperature).toFixed(1) + ' °C';
}

// i18n Engine (ID & EN)
const translations = {
    id: {
        nav_telemetry: "Telemetri Real-Time", nav_kinetics: "Model Kinetika & AI", nav_history: "Riwayat Batch Log", nav_hardware: "Hardware ESP32-C3", nav_developer: "Tentang Pengembang",
        system_status: "Status Node", badge_skripsi: "Riset UMKM Tahu Sumedang", visualizer_title: "Indikator Aktuator Hardware", status_fermenting: "Fermentasi", status_optimal: "pH Optimal", status_over: "Terlalu Asam",
        btn_reset: "Reset Batch", btn_pause: "Jeda", btn_resume: "Lanjutkan", metric_ph_title: "pH Soft Sensor", metric_temp_title: "Suhu (NTC 10K)", metric_k_title: "Laju Reaksi k(T)", metric_timer_title: "Durasi Batch", metric_water_title: "Sensor Air Tahu",
        chart_title: "Kurva Kinetika pH(t) & Suhu Real-Time", chart_subtitle: "Rentang Optimal Penggumpalan Tahu Sumedang Ditandai Warna Hijau (pH 4.2 - 5.2)"
    },
    en: {
        nav_telemetry: "Real-Time Telemetry", nav_kinetics: "Kinetics & AI Model", nav_history: "Batch History Log", nav_hardware: "ESP32-C3 Hardware", nav_developer: "About Developer",
        system_status: "Node Status", badge_skripsi: "Tahu Sumedang UMKM Research", visualizer_title: "Hardware Actuator Indicator", status_fermenting: "Fermenting", status_optimal: "Optimal pH", status_over: "Over-Fermented",
        btn_reset: "Reset Batch", btn_pause: "Pause", btn_resume: "Resume", metric_ph_title: "pH Soft Sensor", metric_temp_title: "Temp (NTC 10K)", metric_k_title: "Reaction Rate k(T)", metric_timer_title: "Batch Duration", metric_water_title: "Soy Whey Level",
        chart_title: "Real-Time pH(t) & Temperature Kinetic Curve", chart_subtitle: "Optimal Tahu Curdling Range Highlighted in Green (pH 4.2 - 5.2)"
    }
};

let currentLang = 'id';

function setLanguage(lang) {
    currentLang = lang;
    document.getElementById('current-lang-code').innerText = lang.toUpperCase();
    document.getElementById('current-lang-flag').innerText = lang === 'id' ? '🇮🇩' : '🇬🇧';
    document.getElementById('lang-menu').classList.add('hidden');

    const dict = translations[lang];
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (dict[key]) el.innerText = dict[key];
    });

    if (telemetryChart) {
        telemetryChart.data.datasets[0].label = lang === 'id' ? 'Soft Sensor pH(t)' : 'pH Soft Sensor(t)';
        telemetryChart.data.datasets[1].label = lang === 'id' ? 'Suhu NTC (°C)' : 'NTC Temp (°C)';
        telemetryChart.update('none');
    }
}

function toggleLangDropdown() { document.getElementById('lang-menu').classList.toggle('hidden'); }

// Light / Dark Theme Switcher
let isDarkMode = true;
function toggleTheme() {
    isDarkMode = !isDarkMode;
    const htmlTag = document.documentElement;
    const themeIcon = document.getElementById('theme-icon');

    if (isDarkMode) {
        htmlTag.setAttribute('data-theme', 'dark');
        htmlTag.classList.add('dark');
        themeIcon.className = 'fa-solid fa-sun text-amber-400 text-base';
    } else {
        htmlTag.setAttribute('data-theme', 'light');
        htmlTag.classList.remove('dark');
        themeIcon.className = 'fa-solid fa-moon text-slate-700 text-base';
    }

    if (telemetryChart) {
        const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';
        const textColor = isDarkMode ? '#94a3b8' : '#64748b';
        telemetryChart.options.scales.x.grid.color = gridColor;
        telemetryChart.options.scales.x.ticks.color = textColor;
        telemetryChart.options.scales.y.grid.color = gridColor;
        telemetryChart.options.scales.y.ticks.color = textColor;
        telemetryChart.options.scales.yTemp.ticks.color = textColor;
        telemetryChart.update();
    }
}

// Web Audio Alarm Synthesizer
let audioCtx = null;
let isMuted = false;

function initAudio() {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
}

function toggleAudio() {
    isMuted = !isMuted;
    const icon = document.getElementById('audio-icon');
    const text = document.getElementById('buzzer-state-text');
    if (isMuted) {
        icon.className = 'fa-solid fa-volume-xmark text-slate-500 text-base';
        text.innerText = currentLang === 'id' ? 'Mute (Hening)' : 'Muted';
    } else {
        icon.className = 'fa-solid fa-volume-high text-emerald-400 text-base';
        text.innerText = currentLang === 'id' ? 'Aktif (Auto-Tone)' : 'Active (Auto-Tone)';
        playBeep(800, 0.1);
    }
}

function playBeep(freq, duration) {
    if (isMuted) return;
    try {
        initAudio();
        if (audioCtx.state === 'suspended') audioCtx.resume();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + duration);
    } catch (e) {}
}

// Chart.js Setup
let telemetryChart = null;

function initChart() {
    const ctx = document.getElementById('telemetryChart').getContext('2d');
    const optimalRangePlugin = {
        id: 'optimalRangeHighlight',
        beforeDraw(chart) {
            const { ctx, chartArea: { left, top, width, height }, scales: { y } } = chart;
            if (!y) return;
            const yTop = y.getPixelForValue(5.2);
            const yBottom = y.getPixelForValue(4.2);
            ctx.save();
            ctx.fillStyle = isDarkMode ? 'rgba(16, 185, 129, 0.12)' : 'rgba(16, 185, 129, 0.18)';
            ctx.fillRect(left, yTop, width, yBottom - yTop);
            ctx.restore();
        }
    };

    telemetryChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                { label: 'Soft Sensor pH(t)', data: [], borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', borderWidth: 3, fill: true, tension: 0.3, yAxisID: 'y' },
                { label: 'Suhu NTC (°C)', data: [], borderColor: '#22d3ee', borderWidth: 2, borderDash: [5, 5], pointRadius: 0, fill: false, tension: 0.2, yAxisID: 'yTemp' }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false, animation: false,
            scales: {
                x: { title: { display: true, text: 'Waktu Record', color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.08)' }, ticks: { color: '#94a3b8' } },
                y: { min: 3.0, max: 7.0, title: { display: true, text: 'pH Level', color: '#f59e0b' }, grid: { color: 'rgba(255,255,255,0.08)' }, ticks: { color: '#94a3b8' } },
                yTemp: { position: 'right', min: 15, max: 50, title: { display: true, text: 'Suhu (°C)', color: '#22d3ee' }, grid: { drawOnChartArea: false }, ticks: { color: '#94a3b8' } }
            },
            plugins: { legend: { display: false } }
        },
        plugins: [optimalRangePlugin]
    });
}

// Tab Switching
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.className = "nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 text-slate-400 hover:bg-slate-800/50 hover:text-slate-200";
    });

    document.getElementById(`tab-${tabId}`).classList.remove('hidden');
    const activeBtn = document.getElementById(`nav-${tabId}`);
    if (activeBtn) activeBtn.className = "nav-btn active w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 bg-amber-500/10 text-amber-500 border border-amber-500/30 font-semibold";

    const pageTitle = document.getElementById('page-title');
    const dict = translations[currentLang];
    if (tabId === 'telemetry') pageTitle.innerText = dict.nav_telemetry;
    else if (tabId === 'kinetics') pageTitle.innerText = dict.nav_kinetics;
    else if (tabId === 'history') pageTitle.innerText = dict.nav_history;
    else if (tabId === 'hardware') pageTitle.innerText = dict.nav_hardware;
    else if (tabId === 'developer') pageTitle.innerText = dict.nav_developer;
}

window.onload = function() {
    let progress = 0;
    const loaderBar = document.getElementById('loader-bar');
    const loaderText = document.getElementById('loader-text');
    const preloader = document.getElementById('preloader');

    const interval = setInterval(() => {
        progress += 20;
        if (progress > 100) progress = 100;
        if (loaderBar) loaderBar.style.width = progress + '%';
        if (loaderText) loaderText.innerText = `Memuat System... ${progress}%`;

        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                if (preloader) {
                    preloader.classList.add('opacity-0');
                    setTimeout(() => preloader.style.display = 'none', 500);
                }
            }, 300);
        }
    }, 100);

    initChart();
    fetchDashboardData();
    setInterval(fetchDashboardData, 3000);
    window.addEventListener('click', initAudio, { once: true });
};