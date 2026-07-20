# Setup Environment Windows 11 & Dokumentasi Cross-Platform

Rencana untuk menginstal tools yang kurang, menjalankan proyek, dan membuat dokumentasi setup yang terpisah antara Windows dan macOS.

## Temuan Penting

> [!NOTE]
> Situasinya lebih baik dari perkiraan awal:
> - Database menggunakan **SQLite** (bukan PostgreSQL) — tidak perlu install database server
> - Folder `vendor/` dan `node_modules/` sudah ada dari Mac — dependencies sudah terinstal
> - File `database.sqlite` sudah ada dengan data (266KB)
> - npm sebenarnya ada di `C:\Program Files\nodejs\`, hanya PATH shell yang bermasalah

---

## Fase 1: Fix & Install Tools

### 1A. Fix npm PATH
npm sudah terinstal di `C:\Program Files\nodejs\npm.cmd` tapi tidak bisa dipanggil langsung. Saya akan menambahkan path Node.js ke environment `$env:PATH` saat menjalankan perintah.

### 1B. Install Composer via `winget`
- `winget` sudah tersedia di sistem
- Akan menjalankan: `winget install Composer.Composer`
- Atau download langsung Composer-Setup.exe

---

## Fase 2: Validasi & Jalankan Proyek

### 2A. Backend Laravel
1. Jalankan `php artisan migrate:status` — cek apakah semua migration sudah applied
2. Jalankan `php artisan db:seed --class=RoleSeeder` jika perlu
3. Jalankan `php artisan serve` — start development server di `http://localhost:8000`
4. Jalankan `npm run dev` (Vite) — untuk asset compilation

### 2B. Mobile App (Expo)
1. Jalankan `npm start` dari folder `mobile/` — start Expo dev server
2. Verifikasi QR code muncul untuk testing

---

## Fase 3: Dokumentasi Cross-Platform

### [NEW] `docs/SETUP.md`
Membuat file dokumentasi setup lengkap yang terpisah antara:

#### Bagian Windows
- Prasyarat (PHP, Node.js, Composer, Git)
- Cara install masing-masing tool via `winget` / installer
- Cara menjalankan backend (`php artisan serve`) dan mobile (`npx expo start`)
- Troubleshooting Windows-specific (PATH issues, php.ini location, dll)

#### Bagian macOS
- Prasyarat (Homebrew, PHP, Node.js, Composer)
- Cara install via `brew`
- Cara menjalankan backend dan mobile
- Troubleshooting macOS-specific (Xcode, iOS Simulator, dll)

#### Bagian Umum
- Konfigurasi `.env`
- Database setup (SQLite)
- Seed data & akun demo
- API endpoints untuk mobile

### [MODIFY] `docs/PRD.md`
- Menambahkan referensi ke `SETUP.md` untuk panduan instalasi

---

## Verification Plan

### Automated
- `php artisan migrate:status` — semua migration harus "Ran"
- `php artisan serve` — server berjalan tanpa error
- `npm run dev` (di backend) — Vite berhasil compile
- `npm start` (di mobile) — Expo server berjalan

### Manual
- Buka `http://localhost:8000/admin` di browser — Filament dashboard muncul
- Login dengan `admin@inventory.test` / `password` — berhasil masuk
