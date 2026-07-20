# 🛠️ Panduan Setup Development Environment

Dokumen ini menjelaskan cara menyiapkan lingkungan pengembangan untuk **Sistem Manajemen Inventaris** di dua platform: **Windows 11** dan **macOS**.

---

## Arsitektur Proyek

```
inventory_mgm/
├── backend/          # Laravel 13 + Filament 5 (Web Dashboard & API)
│   ├── .env          # Konfigurasi environment
│   ├── artisan       # Laravel CLI
│   ├── composer.json # Dependensi PHP
│   └── database/
│       └── database.sqlite  # Database SQLite
├── mobile/           # React Native (Expo) — Aplikasi Staf Gudang
│   ├── App.js        # Entry point navigasi
│   ├── package.json  # Dependensi Node.js
│   └── src/screens/  # Halaman aplikasi
└── docs/             # Dokumentasi proyek
```

### Tech Stack
| Komponen | Teknologi | Versi Minimum |
|----------|-----------|---------------|
| Backend | PHP | >= 8.4 |
| Framework | Laravel | 13.x |
| Admin Panel | Filament | 5.x |
| Auth | Laravel Sanctum | 4.x |
| RBAC | Spatie Permission | 8.x |
| Database | SQLite | 3.x (bawaan PHP) |
| Mobile | React Native + Expo | SDK 57 |
| Mobile Runtime | Node.js | >= 20.x |

---

## 🪟 Setup di Windows 11

### 1. Prasyarat

#### PHP 8.4+
```powershell
# Install via winget
winget install PHP.PHP.8.4 --source winget

# Atau download manual dari https://windows.php.net/download/
# Extract ke C:\php dan tambahkan ke PATH

# Verifikasi
php --version
```

> **Catatan:** Setelah install, pastikan ekstensi berikut aktif di `php.ini`:
> - `extension=pdo_sqlite`
> - `extension=sqlite3`
> - `extension=mbstring`
> - `extension=curl`
> - `extension=fileinfo`
> - `extension=openssl`
> - `extension=zip`
> - `extension=gd`

Lokasi `php.ini`:
```powershell
php --ini
```

#### Composer
```powershell
# Install via winget
winget install Composer.Composer --source winget

# Atau download installer dari https://getcomposer.org/Composer-Setup.exe

# Verifikasi
composer --version
```

#### Node.js + npm
```powershell
# Install via winget
winget install OpenJS.NodeJS.LTS --source winget

# Verifikasi
node --version
npm --version
```

> **Troubleshooting npm tidak terdeteksi:**
> Jika `npm` tidak bisa dipanggil tapi `node` bisa, coba:
> ```powershell
> # Opsi 1: Panggil langsung via .cmd
> & "C:\Program Files\nodejs\npm.cmd" --version
>
> # Opsi 2: Fix Execution Policy (run as Administrator)
> Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned
>
> # Opsi 3: Restart terminal / komputer setelah install
> ```

#### Git
```powershell
winget install Git.Git --source winget
git --version
```

---

### 2. Setup Backend (Laravel)

```powershell
cd backend

# Install dependensi PHP
composer install

# Salin file konfigurasi (jika belum ada)
copy .env.example .env

# Generate application key (jika belum ada)
php artisan key:generate

# Jalankan migrasi database
php artisan migrate

# Seed data demo (role, user, produk, transaksi)
php artisan db:seed
```

### 3. Menjalankan Backend

**Opsi 1: Server saja**
```powershell
php artisan serve
# Backend berjalan di http://localhost:8000
# Dashboard admin: http://localhost:8000/admin
```

**Opsi 2: Server + Vite (untuk hot-reload CSS/JS)**
```powershell
# Terminal 1
php artisan serve

# Terminal 2
npm install
npm run dev
```

**Opsi 3: Semua sekaligus (via Composer script)**
```powershell
npm install
composer dev
# Menjalankan: Laravel server + Queue worker + Pail logs + Vite
```

### 4. Setup Mobile App (Expo)

```powershell
cd mobile

# Install dependensi
npm install

# Jalankan Expo dev server
npx expo start

# Untuk Android emulator
npx expo start --android

# Untuk web browser
npx expo start --web
```

> **Catatan Windows:** iOS Simulator tidak tersedia di Windows. Gunakan:
> - Expo Go di HP Android fisik (scan QR code)
> - Android Emulator via Android Studio
> - Web browser (`--web`)

---

## 🍎 Setup di macOS

### 1. Prasyarat

#### Homebrew (Package Manager)
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

#### PHP 8.4+
```bash
brew install php@8.4

# Verifikasi
php --version
```

#### Composer
```bash
brew install composer

# Verifikasi
composer --version
```

#### Node.js + npm
```bash
brew install node

# Atau via nvm (recommended)
brew install nvm
nvm install --lts
nvm use --lts

# Verifikasi
node --version
npm --version
```

#### Git
```bash
# Biasanya sudah terinstal via Xcode Command Line Tools
xcode-select --install
git --version
```

---

### 2. Setup Backend (Laravel)

```bash
cd backend

# Install dependensi PHP
composer install

# Salin file konfigurasi (jika belum ada)
cp .env.example .env

# Generate application key (jika belum ada)
php artisan key:generate

# Jalankan migrasi database
php artisan migrate

# Seed data demo
php artisan db:seed
```

### 3. Menjalankan Backend

**Opsi 1: Server saja**
```bash
php artisan serve
# Backend berjalan di http://localhost:8000
# Dashboard admin: http://localhost:8000/admin
```

**Opsi 2: Server + Vite (untuk hot-reload CSS/JS)**
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm install && npm run dev
```

**Opsi 3: Semua sekaligus (via Composer script)**
```bash
npm install
composer dev
# Menjalankan: Laravel server + Queue worker + Pail logs + Vite
```

### 4. Setup Mobile App (Expo)

```bash
cd mobile

# Install dependensi
npm install

# Jalankan Expo dev server
npx expo start

# Untuk iOS Simulator (macOS only)
npx expo start --ios

# Untuk Android emulator
npx expo start --android

# Untuk web browser
npx expo start --web
```

> **Catatan macOS:** Untuk menjalankan iOS Simulator, pastikan Xcode sudah terinstal:
> ```bash
> xcode-select --install
> # Buka Xcode > Settings > Platforms > Install iOS Simulator
> ```

---

## ⚙️ Konfigurasi Environment (.env)

File `.env` di folder `backend/` mengontrol konfigurasi aplikasi. Berikut pengaturan penting:

```env
# Nama Aplikasi
APP_NAME="Inventory Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database (Default: SQLite — tidak perlu install database server)
DB_CONNECTION=sqlite

# Jika ingin beralih ke PostgreSQL:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=inventory_mgm
# DB_USERNAME=postgres
# DB_PASSWORD=your_password
```

> **SQLite** adalah default dan sudah cukup untuk development. File database otomatis dibuat di `database/database.sqlite`.

---

## 👤 Akun Demo

Setelah menjalankan `php artisan db:seed`, tersedia 3 akun:

| Role | Email | Password |
|------|-------|----------|
| **Super Admin** | `admin@inventory.test` | `password` |
| **Manager** | `manager@inventory.test` | `password` |
| **Staff** | `staff@inventory.test` | `password` |

### Hak Akses per Role:
- **Manager** — Akses operasional tertinggi: Produk, Supplier, Transaksi, Laporan, Approve
- **Super Admin** — Administrasi IT: Membuat/menghapus akun, Audit Log
- **Staff** — Operasional gudang: Input barang masuk/keluar, scan barcode

---

## 🔌 API Endpoints (untuk Mobile App)

Base URL: `http://localhost:8000/api`

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/login` | Autentikasi (dapat Sanctum token) |
| GET | `/dashboard` | Data dashboard per role |
| GET | `/dashboard/chart` | Data grafik Inbound vs Outbound (7 hari) |
| GET | `/products` | Daftar produk |
| GET | `/products/{barcode}` | Cari produk via barcode |
| POST | `/transactions` | Buat transaksi Inbound/Outbound |

> **Header wajib untuk endpoint terproteksi:**
> ```
> Authorization: Bearer {sanctum_token}
> Accept: application/json
> ```

---

## ❓ Troubleshooting

### Windows

| Masalah | Solusi |
|---------|--------|
| `npm` command not found | Jalankan `Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned` sebagai Administrator, atau gunakan `npm.cmd` |
| PHP extension not loaded | Edit `php.ini`, hapus `;` di depan `extension=xxx` yang dibutuhkan |
| `php artisan` error "Composer dependencies require PHP >= 8.4" | Upgrade PHP ke 8.4+ dan jalankan ulang `composer install` |
| `SQLSTATE[HY000]: could not find driver` | Pastikan `extension=pdo_sqlite` aktif di `php.ini` |
| Port 8000 already in use | Gunakan `php artisan serve --port=8001` |

### macOS

| Masalah | Solusi |
|---------|--------|
| `php` command links to old version | `brew link php@8.4 --force` |
| iOS Simulator not found | Install via Xcode > Settings > Platforms |
| Permission denied on `storage/` | `chmod -R 775 storage bootstrap/cache` |
| `vendor/` folder missing | Jalankan `composer install` |
