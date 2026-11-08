# 🧀 TahuGuard - ESP32-C3 Edge AIoT Smart Fermentation System
> **Riset UMKM Tahu Sumedang** | Sistem Monitoring Telemetri Real-Time & Estimasi Soft Sensor Kinetika Fermentasi Tahu

---

## 📌 Penjelasan Tema Proyek Kolaboratif

**TahuGuard** adalah sistem pemantauan fermentasi air biang tahu berbasis *Edge AIoT* (Artificial Intelligence of Things) yang dirancang khusus untuk meningkatkan presisi penggumpalan pada industri UMKM Tahu Sumedang. 

Dalam proses tradisional, pengujian keasaman cairan whey asam sering kali merusak sensor pH kaca akibat suhu tinggi dan sifat korosif asam. **TahuGuard** menyelesaikan masalah ini dengan mengimplementasikan **Soft Sensor Kinetika Orde Pertama** yang mengekstrapolasi nilai pH berdasarkan variabel suhu NTC 10K dan waktu fermentasi. Data diproses secara *real-time* via microservice WebSocket Node.js, disajikan pada Dashboard Laravel 13, dan terintegrasi otomatis dengan Telegram Bot Alert.

---

## 👥 Tim Pengembang (Contributors)

| Nama Pengembang | Peran / Spesialisasi | Kontak |
| :--- | :--- | :--- |
| **Haikal Luthfianino Balukia** | Embedded Systems Specialist | `haikalluthfianinobalukia@proton.me` |
| **Muzayin Jamil** | Backend Lead Engineer | `zeinofficial19@gmail.com` |
| **Raidan Arya Pradana** | IoT Hardware & Actuators | `aryapradanaraidan@gmail.com` |
| **Piere Anggadipraja Sukandi** | UI/UX & Frontend Engineer | `wetansumedang91@gmail.com` |
| **Dani Sonjaya** | Network & Telemetry Analyst | `danisonjaya73@gmail.com` |

---

## 📸 Tangkapan Layar (Screenshots) Dashboard Web

Simpan gambar screenshot di folder `docs/screenshots/` dengan nama file berikut:

### 1. Telemetri Real-Time
Memantau Soft Sensor pH, Suhu (NTC 10K), Kelembaban, Status Aktuator, dan Visualisasi Grafik Live.
![Telemetri Real-Time](docs/screenshots/telemetri-realtime.png)

### 2. Model Kinetika & AI
Formulasi matematis orde pertama untuk estimasi penurunan pH tanpa sensor fisik korosif.
![Model Kinetika AI](docs/screenshots/model-kinetika.png)

### 3. Riwayat Batch Log
Rekam jejak histori telemetri per batch produksi dilengkapi fitur ekspor laporan berkas CSV.
![Riwayat Batch Log](docs/screenshots/riwayat-log.png)

### 4. Hardware ESP32-C3
Informasi spesifikasi modul mikrokontroler RISC-V Micro-Edge yang digunakan pada unit node.
![Hardware Specs](docs/screenshots/hardware-specs.png)

### 5. Tentang Pengembang
Halaman informasi profil dan pembagian tugas tim kolaboratif TahuGuard.
![Tentang Pengembang](docs/screenshots/tentang-pengembang.png)

---

## 🧮 Model Kinetika Penggumpalan (Soft Sensor)

Persamaan kinetika yang diimplementasikan pada sistem untuk menggantikan sensor pH kaca:

$$k(T) = 0.25 + 0.015 \times (T - 30.0)$$

$$pH(t) = 3.5 + 3.3 \times e^{-k \times t}$$

* **Rentang pH Optimal Penggumpalan:** $pH\ 4.2 - 5.2$ (Warna Hijau / Status Normal)
* **Status Critical / Fermentasi Basi:** $pH < 4.2$ (Trigger Alarm Web Audio & Telegram Alert)

---

## 🛠️ Arsitektur & Teknologi

* **Firmware:** ESP32-C3 RISC-V Single-core @ 160MHz (C++ / Arduino Framework)
* **Backend Framework:** Laravel 13 (PHP 8.x) & MySQL Database
* **Real-Time Communication:** Node.js Socket.io Microservice (Port 6001)
* **Frontend:** Blade, Tailwind CSS, Chart.js, Web Audio API Synth
* **Notification Integration:** Telegram Bot API Webhook