# 📚 Index Dokumentasi — Enterprise Inventory Management System

Semua dokumentasi proyek tersedia di folder `/docs`. Berikut panduan membaca dan menggunakannya.

---

## 🗺️ Peta Dokumen

| File | Deskripsi | Untuk Siapa |
|------|-----------|-------------|
| [PRD.md](./PRD.md) | Product Requirements Document — Fitur, peran pengguna, alur bisnis | Developer, Product Manager |
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
│   ├── PRD.md                      ← Product Requirements
│   ├── SETUP.md                    ← Setup lokal
│   ├── DOCKER.md                   ← Panduan Docker (NEW)
│   ├── DEPLOY_VPS.md               ← Deploy ke VPS (NEW)
│   └── BUILD_MOBILE.md             ← Build APK/IPA (NEW)
│
├── docker-compose.yml              ← Orkestrasi kontainer (dev)
├── Makefile                        ← Shortcut perintah (Linux/Mac/WSL)
└── docker-manage.ps1               ← Shortcut perintah (Windows)
```

---

## 🔑 Akun Default (Untuk Testing)

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| Super Admin | `admin@inventory.test` | `password` | Web + Mobile (semua fitur) |
| Manager | `manager@inventory.test` | `password` | Web (laporan + monitoring) |
| Staff Gudang | `staff@inventory.test` | `password` | Mobile (scan + transaksi) |

---

## 🔗 URL Akses

| Platform | URL (Development) | URL (Production) |
|----------|------------------|-----------------|
| Web Admin Dashboard | `http://localhost:8000/admin` | `https://domain-anda.com/admin` |
| API Endpoint | `http://localhost:8000/api` | `https://domain-anda.com/api` |
| Mobile (Web Preview) | `http://localhost:8081` | APK/IPA (via EAS Build) |
| Database | `localhost:5432` | Tidak diekspos ke publik |
