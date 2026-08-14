# Enterprise Inventory Management System

Sistem Manajemen Inventaris full-stack: **Web Dashboard (Laravel + Filament)** untuk admin/manager/staff dan **Mobile App (React Native + Expo)** untuk staf gudang — berjalan di atas **Docker** dengan **PostgreSQL 16**.

> Status pengembangan terbaru: lihat [docs/CHECKPOINT.md](./docs/CHECKPOINT.md)

---

## Fitur Utama

| Area | Fitur |
|---|---|
| **Autentikasi** | Login dengan 3 akun demo (auto-fill), proteksi brute-force, Laravel Sanctum untuk API mobile |
| **RBAC** | 3 peran: Super Admin, Manajer, Staf — akses per resource dikontrol (Spatie Permission) |
| **Dashboard** | Stats cards gradient + sparkline data nyata 7 hari, line chart Barang Masuk/Keluar 12 bulan, doughnut distribusi stok per kategori (auto-refresh 30 detik) |
| **Notifikasi** | Lonceng real-time dengan badge unread (polling 15 detik), alert low stock & supplier non-aktif, deduplikasi otomatis, persist di database |
| **Produk** | SKU & barcode EAN-13 auto-generate, **SKU reuse** setelah soft-delete (partial unique index), upload gambar, unit dropdown |
| **Supplier** | Form warehouse lengkap (NPWP, ketentuan pembayaran COD/Net 7/14/30/60, status aktif) |
| **Transaksi** | Barang Masuk/Keluar/Penyesuaian Stok, nomor referensi otomatis (`INB-/OUT-/ADJ-`), **anti-fraud** (field immutable, approval mengikuti akun login), stok real-time |
| **Roles Kategori** | CRUD role + permission (khusus Super Admin) |
| **Audit Log** | Activity log semua perubahan (spatie/laravel-activitylog) |
| **Mobile App** | Login, scan barcode, transaksi inbound/outbound, validasi stok — terhubung via REST API |

## Tech Stack

**Backend**
- Laravel 13.8 / PHP 8.3+
- Filament 5.6 (admin panel)
- PostgreSQL 16
- Spatie Permission 8.x (RBAC)
- Laravel Sanctum 4.x (API auth)
- PHPUnit 12 (61 tests / 215 assertions)

**Frontend / Web Assets**
- Vite 8 + Tailwind (CSS kustom dashboard)

**Mobile**
- React Native 0.86 + Expo SDK 57
- React Navigation 7, Axios, expo-camera (scan barcode)

**Infrastruktur**
- Docker Compose (nginx + php-fpm + postgres)
- Windows (PowerShell) & macOS compatible

## Arsitektur

```
┌─────────────────────┐      ┌──────────────────────────┐
│  Mobile App         │      │  Web Dashboard           │
│  (React Native)     │      │  (Laravel + Filament)    │
│  Login / Scan /     │ REST │  Produk / Supplier /     │
│  Transaksi          ├─────►│  Transaksi / Roles /     │
│                     │      │  Laporan & Grafik        │
└─────────────────────┘      └───────────┬──────────────┘
                                         │
                                ┌────────▼─────────┐
                                │  PostgreSQL 16   │
                                │  (Docker volume) │
                                └──────────────────┘
```

```
inventory_mgm/
├── backend/            # Laravel 13 + Filament 5 (web dashboard & API)
├── mobile/             # React Native (Expo) - aplikasi staf gudang
├── docs/               # Dokumentasi lengkap (BRD, PRD, FRD, setup, deploy)
├── backup/             # Skrip & hasil backup database (tidak masuk git)
├── docker-compose.yml  # Orchestrasi app + web + db
└── docker-manage.ps1   # Helper PowerShell untuk Docker
```

## Quick Start (Docker)

**Prasyarat:** Docker Desktop aktif, Node.js 20+ (untuk build asset Vite).

```powershell
# 1. Jalankan container (app, web, db)
docker-compose up -d --build

# 2. Setup aplikasi (hanya pertama kali / setelah clone)
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
docker-compose exec app php artisan storage:link
docker-compose exec app php artisan filament:assets

# 3. Build asset Vite (CSS kustom dashboard) - dari folder backend
npm run build

# 4. Akses
#    Web:    http://localhost:8000/admin
#    Mobile: cd mobile && npm install && npx expo start
```

> **PENTING:** `.env` harus berisi `APP_URL=http://localhost:8000` dan `APP_TIMEZONE=Asia/Jakarta` (template: `backend/.env.example`).

## Akun Demo

| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@inventory.test` | `password` |
| Manajer | `manager@inventory.test` | `password` |
| Staf | `staff@inventory.test` | `password` |

## Testing

```powershell
docker-compose exec app php artisan test
```

**Hasil:** `61 passed (215 assertions)` — mencakup CRUD semua resource, RBAC, anti-fraud transaksi, notifikasi, chart dashboard, SKU reuse, dan API mobile.

## Backup & Restore Database

```powershell
# Backup (file tersimpan di backup/dumps/, tidak masuk git)
.\backup\backup-db.ps1
```

Database tidak disimpan di git — data awal diregenerasi lewat `migrate` + `db:seed`. Untuk data live, gunakan skrip di atas (berbasis `pg_dump`).

## Dokumentasi

| Dokumen | Isi |
|---|---|
| [CHECKPOINT.md](./docs/CHECKPOINT.md) | Status pengembangan, fitur selesai, bug fix, roadmap |
| [BRD.md](./docs/BRD.md) | Business Requirements |
| [PRD.md](./docs/PRD.md) | Product Requirements |
| [FRD.md](./docs/FRD.md) | Functional Requirements |
| [SETUP.md](./docs/SETUP.md) | Setup environment (Windows & macOS) |
| [DOCKER.md](./docs/DOCKER.md) | Panduan Docker harian & migrasi mesin |
| [DEPLOY_VPS.md](./docs/DEPLOY_VPS.md) | Deploy ke VPS |
| [BUILD_MOBILE.md](./docs/BUILD_MOBILE.md) | Build APK/IPA |
| [AKUN_DEMO.md](./docs/AKUN_DEMO.md) | Kredensial demo |