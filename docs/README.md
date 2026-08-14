# 📚 Index Dokumentasi — Enterprise Inventory Management System

Semua dokumentasi proyek tersedia di folder `/docs`. Berikut panduan membaca dan menggunakannya.

---

## 🗺️ Peta Dokumen

| File | Deskripsi | Untuk Siapa |
|------|-----------|-------------|
| [../README.md](../README.md) | **README utama repo** — deskripsi, tech stack, fitur, quick start, akun demo | Semua pengunjung repo / portofolio |
| [CHECKPOINT.md](./CHECKPOINT.md) | **Checkpoint pengembangan** — status terakhir, fitur selesai, bugs fixed, roadmap | Developer, PM, DevOps |
| [BRD.md](./BRD.md) | Business Requirements — tujuan, ruang lingkup, kebutuhan non-fungsional, risiko | Product Manager, Business Owner |
| [PRD.md](./PRD.md) | Product Requirements — fitur, peran pengguna, alur bisnis, acceptance criteria | Developer, Product Manager |
| [FRD.md](./FRD.md) | Functional Requirements — spesifikasi fungsional detail (FR ID), hak akses, API | Developer, QA |
| [AKUN_DEMO.md](./AKUN_DEMO.md) | **Kredensial akun demo** (sumber tunggal untuk testing) | Developer, QA |
| [SETUP.md](./SETUP.md) | Setup awal lingkungan pengembangan (Windows & macOS) | Developer baru |
| [DOCKER.md](./DOCKER.md) | **Panduan Docker** — perintah harian, migrasi mesin, backup DB | Developer |
| [DEPLOY_VPS.md](./DEPLOY_VPS.md) | **Deploy ke VPS** — setup server, HTTPS, monitoring | DevOps, Developer |
| [BUILD_MOBILE.md](./BUILD_MOBILE.md) | **Build APK/IPA** — Android & iOS, cara instalasi | Developer, QA |

---

## 🚀 Mulai Cepat (Quick Start)

### Untuk Developer Baru (Pertama Kali)
1. Baca **[SETUP.md](./SETUP.md)** untuk instalasi tools
2. Baca **[DOCKER.md](./DOCKER.md)** bagian "Pindah Mesin" untuk setup lokal
3. Jalankan: `docker-compose up -d` di folder root proyek
4. Buka: `http://localhost:8000/admin`

### Untuk Pindah Mesin (Windows ↔ Mac)
> Lihat **[DOCKER.md](./DOCKER.md)** → Bagian "Pindah Mesin (Migrasi Antar Komputer)"

### Untuk Deploy ke VPS / Hosting
> Lihat **[DEPLOY_VPS.md](./DEPLOY_VPS.md)** → Ikuti dari Fase 1

### Untuk Build APK / IPA
> Lihat **[BUILD_MOBILE.md](./BUILD_MOBILE.md)** → Pilih metode EAS Build atau Lokal

### Untuk Memahami Produk & Kebutuhan
> Baca secara berurutan: **[BRD.md](./BRD.md)** → **[PRD.md](./PRD.md)** → **[FRD.md](./FRD.md)**

### Untuk Melanjutkan Pengembangan
> Mulai dari **[CHECKPOINT.md](./CHECKPOINT.md)** — status terakhir, fitur selesai, bugs fixed, dan roadmap.

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────┐
│               Sistem Inventaris Terpadu                  │
│                                                          │
│  ┌──────────────────┐     ┌──────────────────────────┐  │
│  │  Mobile App      │     │  Web Dashboard (Admin)   │  │
│  │  (React Native)  │     │  (Laravel + Filament)    │  │
│  │                  │     │                          │  │
│  │  • Login Staf    │     │  • Manajemen Produk      │  │
│  │  • Scan Barcode  │     │  • Manajemen Supplier    │  │
│  │  • Transaksi     │◀───▶│  • Laporan & Grafik      │  │
│  │  • Dashboard     │ API │  • Manajemen User        │  │
│  └──────────────────┘     └──────────────────────────┘  │
│            │                          │                  │
│            └─────────┬────────────────┘                  │
│                      ▼                                   │
│           ┌──────────────────────┐                       │
│           │  PostgreSQL Database │                       │
│           │  (Docker Container)  │                       │
│           └──────────────────────┘                       │
└─────────────────────────────────────────────────────────┘
```

---

## 📂 Struktur Proyek

```
inventory_mgm/
│
├── 📁 backend/                     ← Laravel 13 API + Filament Admin
│   ├── app/
│   │   ├── Http/Controllers/Api/   ← API Controllers (Auth, Product, Transaction)
│   │   ├── Models/                 ← Eloquent Models (Product, Transaction, dll.)
│   │   ├── Filament/               ← Filament Resources (UI Web Admin)
│   │   └── Services/               ← Business Logic (InventoryService)
│   ├── routes/api.php              ← Definisi semua endpoint API
│   ├── database/
│   │   ├── migrations/             ← Skema database
│   │   └── seeders/                ← Data awal (user, produk, supplier)
│   ├── Dockerfile                  ← Image PHP 8.4-FPM
│   └── nginx/default.conf          ← Konfigurasi Nginx
│
├── 📁 mobile/                      ← React Native (Expo) Mobile App
│   ├── App.js                      ← Entry point + konfigurasi navigasi
│   └── src/
│       ├── screens/
│       │   ├── LoginScreen.js      ← Halaman login (Sanctum API)
│       │   ├── DashboardScreen.js  ← Dashboard staf/manager
│       │   ├── ScannerScreen.js    ← Kamera barcode scanner
│       │   └── TransactionFormScreen.js ← Form inbound/outbound
│       └── services/
│           └── api.js              ← Axios HTTP client terkonfigurasi
│
├── 📁 docs/                        ← 📚 SEMUA DOKUMENTASI ADA DI SINI
│   ├── README.md                   ← File ini (index dokumentasi)
│   ├── CHECKPOINT.md               ← Checkpoint pengembangan terakhir
│   ├── BRD.md                      ← Business Requirements
│   ├── PRD.md                      ← Product Requirements
│   ├── FRD.md                      ← Functional Requirements
│   ├── AKUN_DEMO.md                ← Kredensial akun demo
│   ├── SETUP.md                    ← Setup lokal
│   ├── DOCKER.md                   ← Panduan Docker
│   ├── DEPLOY_VPS.md               ← Deploy ke VPS
│   └── BUILD_MOBILE.md             ← Build APK/IPA
│
├── docker-compose.yml              ← Orkestrasi kontainer (dev)
├── Makefile                        ← Shortcut perintah (Linux/Mac/WSL)
└── docker-manage.ps1               ← Shortcut perintah (Windows)
```

---

## 🔑 Akun Default (Untuk Testing)

> Detail lengkap dan hak akses per peran ada di **[AKUN_DEMO.md](./AKUN_DEMO.md)**.

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@inventory.test` | `password` |
| Manager | `manager@inventory.test` | `password` |
| Staff Gudang | `staff@inventory.test` | `password` |

---

## 🔗 URL Akses

| Platform | URL (Development) | URL (Production) |
|----------|------------------|-----------------|
| Web Admin Dashboard | `http://localhost:8000/admin` | `https://domain-anda.com/admin` |
| API Endpoint | `http://localhost:8000/api` | `https://domain-anda.com/api` |
| Mobile (Web Preview) | `http://localhost:8081` | APK/IPA (via EAS Build) |
| Database | `localhost:5432` | Tidak diekspos ke publik |
