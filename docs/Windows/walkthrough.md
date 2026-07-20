# Walkthrough: Setup Environment Windows 11 & Dokumentasi Cross-Platform

Seluruh pekerjaan setup lingkungan pengembangan dari macOS ke Windows 11 telah berhasil diselesaikan. Berikut adalah rincian lengkap dari apa yang dilakukan:

---

## 1. Upgrade PHP 8.3 → 8.4.23

PHP 8.3.31 yang sudah terinstal via winget ternyata **terlalu rendah** — dependencies proyek (yang di-resolve di Mac) membutuhkan PHP >= 8.4.1.

**Yang dilakukan:**
- Download PHP 8.4.23 (Thread Safe, x64) langsung dari `windows.php.net`
- Extract ke `C:\php84`
- Konfigurasi `php.ini`:
  - Aktifkan 11 ekstensi (pdo_sqlite, sqlite3, curl, mbstring, openssl, gd, zip, bcmath, fileinfo, intl, exif)
  - Set `extension_dir` ke `C:\php84\ext`
  - Tambahkan CA certificate bundle (`cacert.pem`) untuk SSL/TLS
- Tambahkan `C:\php84` ke User PATH (menggantikan PHP 8.3)

---

## 2. Install Composer 2.10.2

**Yang dilakukan:**
- Download Composer installer via `getcomposer.org`
- Install `composer.phar` ke `C:\php84\composer.phar`
- Buat `composer.bat` wrapper untuk kemudahan akses sebagai command
- Jalankan `composer install` di folder `backend/` — **26 packages discovered**, termasuk Filament upgrade

---

## 3. Validasi & Jalankan Proyek

### Backend (Laravel + Filament)
- ✅ `php artisan migrate:status` — **11 migrations** semua sudah "Ran" (database SQLite dari Mac masih utuh)
- ✅ `php artisan serve` — Server berjalan di `http://127.0.0.1:8000`
- ✅ `npm install` — 64 packages, 0 vulnerabilities
- ✅ `npm run dev` (Vite v8.1.3) — Berjalan di `http://localhost:5173`

### Verifikasi Dashboard
- ✅ Login sebagai Super Admin (`admin@inventory.test`) — **berhasil**
- ✅ Dashboard menampilkan:
  - 4 Stats Widgets (Total Produk, Stok Menipis, Inbound, Outbound)
  - Grafik Tren Transaksi (Line Chart — 3 Tahun)
  - Grafik Distribusi Kategori (Doughnut Chart)
  - Tabel Transaksi Terkini
  - Sidebar navigasi lengkap (Products, Categories, Suppliers, Transactions, Users, Activity Logs)

---

## 4. Dokumentasi Cross-Platform

### File Baru: [SETUP.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/SETUP.md)

Dokumentasi setup lengkap yang terpisah antara **Windows 11** dan **macOS**, mencakup:
- Prasyarat dan cara install tools (PHP, Composer, Node.js, Git)
- Setup backend (composer install, migrate, seed)
- Menjalankan server (3 opsi: artisan serve, dengan Vite, atau `composer dev`)
- Setup mobile app (Expo)
- Konfigurasi `.env`
- Akun demo dan API endpoints
- Troubleshooting per platform

### File Dimodifikasi: [PRD.md](file:///C:/Users/Rahardyan/Desktop/Project/inventory_mgm/docs/PRD.md)
- Ditambahkan link referensi ke `SETUP.md` di bagian bawah dokumen

---

## Status Akhir

| Komponen | Status | URL |
|----------|--------|-----|
| Laravel Server | ✅ Running | http://127.0.0.1:8000 |
| Filament Dashboard | ✅ Running | http://127.0.0.1:8000/admin |
| Vite Dev Server | ✅ Running | http://localhost:5173 |
| PHP | 8.4.23 | `C:\php84\php.exe` |
| Composer | 2.10.2 | `C:\php84\composer.bat` |
| Node.js | v24.15.0 | `C:\Program Files\nodejs` |
| npm | 11.12.1 | `C:\Program Files\nodejs\npm.cmd` |

> [!TIP]
> **Untuk menjalankan proyek di sesi terminal baru**, pastikan PATH mencakup `C:\php84` dan `C:\Program Files\nodejs`. Atau gunakan perintah ini di awal sesi PowerShell:
> ```powershell
> $env:PATH = "C:\php84;C:\Program Files\nodejs;" + $env:PATH
> ```
> Setelah itu, Anda bisa langsung menjalankan `php artisan serve` di folder `backend/`.
