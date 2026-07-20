# 🖥️ Laporan Lingkungan Pengembangan — Windows 11

> Diperiksa: 9 Juli 2026

---

## 1. Ringkasan Dokumen Planning

Proyek **Inventory Management System** adalah sistem manajemen inventaris skala enterprise dengan 3 komponen utama:

| Dokumen | Isi Utama | Status |
|---|---|---|
| [PRD.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/PRD.md) | Akun demo (Super Admin, Manager, Staff) + fitur mobile | ✅ Lengkap |
| [BRD.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/BRD.md) | Kebutuhan bisnis: RBAC, Audit Trail, Skalabilitas, Keamanan | ✅ Lengkap |
| [FRD.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/FRD.md) | Modul fungsional: CRUD, Transaksi Inbound/Outbound, Notifikasi, Laporan | ✅ Lengkap |

### Implementation Plans (4 Dokumen)

| Plan | Fokus | Status |
|---|---|---|
| [implementation_plan.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/implementation_plan.md) | Integrasi Mobile-Web, CRUD, Role System | ✅ Selesai |
| [implementation_plan_2.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/implementation_plan_2.md) | Dashboard role-based (grafik Manager/Admin, inbound Staff) | ✅ Selesai |
| [implementation_plan_dashboard_customization.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/implementation_plan_dashboard_customization.md) | Kustomisasi Dashboard Filament (Stats, Charts, Tables) | ✅ Selesai |
| [implementation_plan_filament_chart.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/implementation_plan_filament_chart.md) | Grafik Inbound 3 Tahun Terakhir | ✅ Selesai |

### Task & Walkthrough

| Dokumen | Keterangan |
|---|---|
| [task.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/task.md) | 6/6 task selesai — Dashboard widgets |
| [task_2.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/task_2.md) | 11/11 task selesai — Mobile & Role System |
| [walkthrough.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/walkthrough.md) | Custom Dashboard Filament (Stats, Charts, Role-based) |
| [walkthrough_2.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/walkthrough_2.md) | Integrasi Mobile, User Management, Role hierarchy |

> [!NOTE]
> **Semua task sebelumnya sudah SELESAI.** Tidak ada task yang tertinggal atau in-progress.

---

## 2. Tech Stack yang Dibutuhkan

Berdasarkan dokumen planning, proyek ini membutuhkan:

| Komponen | Teknologi | Kegunaan |
|---|---|---|
| **Backend** | Laravel (PHP) + Filament | Web dashboard admin |
| **Database** | PostgreSQL | Penyimpanan data |
| **Auth** | Laravel Sanctum + Spatie Permission | Token API & RBAC |
| **Mobile** | React Native / Expo | Aplikasi staf gudang |
| **Runtime** | Node.js + npm | Build tools & mobile dev |

---

## 3. Status Software di Windows 11

### ✅ Sudah Terinstal

| Software | Versi | Status |
|---|---|---|
| **PHP** | 8.3.31 (ZTS x64) | ✅ Siap |
| **Node.js** | v24.15.0 | ✅ Siap |
| **Git** | 2.54.0.windows.1 | ✅ Siap |
| **Docker** | 29.6.1 | ✅ Siap |

### ❌ Belum Terinstal (WAJIB)

| Software | Kebutuhan | Cara Install |
|---|---|---|
| **PostgreSQL** | Database utama aplikasi | Download dari [postgresql.org](https://www.postgresql.org/download/windows/) atau `docker run postgres` |
| **Composer** | Dependency manager PHP (Laravel) | Download dari [getcomposer.org](https://getcomposer.org/download/) |
| **npm** | Package manager Node.js (aneh karena Node.js sudah ada) | Biasanya bundled — coba `corepack enable` atau reinstall Node.js |
| **Python** | Tidak wajib untuk proyek ini, tapi berguna | Microsoft Store atau [python.org](https://www.python.org/downloads/) |

> [!WARNING]
> **npm tidak terdeteksi padahal Node.js v24 sudah ada.** Ini kemungkinan karena instalasi Node.js via `nvm-windows` atau path yang belum terkonfigurasi. Perlu diperbaiki karena mobile app (Expo) butuh npm/npx.

> [!IMPORTANT]
> **3 hal yang HARUS diinstal sebelum bisa menjalankan proyek:**
> 1. **Composer** — tanpa ini, `composer install` tidak bisa dijalankan untuk backend Laravel
> 2. **PostgreSQL** — atau gunakan Docker (`docker-compose up`) jika sudah ada config Docker
> 3. **npm** — perbaiki instalasi Node.js agar npm tersedia, untuk menjalankan mobile app

---

## 4. Rekomendasi Langkah Selanjutnya

1. **Fix npm** — Jalankan `corepack enable` atau reinstall Node.js dari [nodejs.org](https://nodejs.org)
2. **Install Composer** — Download installer dari [getcomposer.org](https://getcomposer.org)
3. **Setup PostgreSQL** — Pilih salah satu:
   - Install native PostgreSQL for Windows
   - Atau gunakan Docker: `docker run --name postgres -e POSTGRES_PASSWORD=secret -p 5432:5432 -d postgres:16`
4. **Clone dependencies** — Setelah tools siap, jalankan `composer install` di folder `backend` dan `npm install` di folder `mobile`
