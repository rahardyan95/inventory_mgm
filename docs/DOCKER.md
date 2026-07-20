# 🐳 Panduan Docker — Enterprise Inventory Management System

Dokumen ini adalah **rujukan utama** untuk semua operasi Docker pada proyek ini:
menjalankan aplikasi, berpindah mesin, serta troubleshooting.

---

## Arsitektur Kontainer

```
┌──────────────────────────────────────────────────┐
│                Docker Network: inventory_network  │
│                                                  │
│  ┌──────────┐    ┌──────────┐   ┌────────────┐  │
│  │  Nginx   │───▶│  PHP-FPM │──▶│ PostgreSQL │  │
│  │  :80     │    │ (app)    │   │ :5432      │  │
│  └──────────┘    └──────────┘   └────────────┘  │
│       ▲                                          │
└───────┼──────────────────────────────────────────┘
        │
   Host: :8000 (http://localhost:8000)
```

| Kontainer        | Image              | Port Host | Keterangan                        |
|------------------|--------------------|-----------|-----------------------------------|
| `inventory_web`  | `nginx:alpine`     | 8000      | Web server, proxy ke PHP-FPM      |
| `inventory_app`  | `php:8.4-fpm-alpine`| –        | Laravel PHP-FPM (tanpa port publik)|
| `inventory_db`   | `postgres:16-alpine`| 5432     | Database PostgreSQL                |

---

## ⚡ Perintah Sehari-hari (Quick Reference)

> Jalankan semua perintah dari folder **root proyek** (`inventory_mgm/`).

### Menjalankan Aplikasi

```powershell
# Jalankan semua kontainer di background
docker-compose up -d

# Cek status kontainer (pastikan semua "Up")
docker-compose ps

# Lihat log real-time semua kontainer
docker-compose logs -f

# Lihat log satu kontainer saja
docker-compose logs -f app
docker-compose logs -f web
docker-compose logs -f db
```

### Menghentikan Aplikasi

```powershell
# Hentikan kontainer (data database TETAP tersimpan)
docker-compose down

# Hentikan kontainer DAN hapus semua data database (HATI-HATI!)
docker-compose down -v
```

### Menggunakan Makefile (Cara Singkat)

```powershell
# Lihat semua perintah yang tersedia
cat Makefile

make up        # docker-compose up -d
make down      # docker-compose down
make build     # docker-compose build
make migrate   # php artisan migrate
make seed      # php artisan db:seed
make fresh     # migrate:fresh --seed (reset semua data)
make bash      # masuk ke shell kontainer app
```

---

## 🔄 Pindah Mesin (Migrasi Antar Komputer)

Ikuti langkah ini persis saat berpindah dari Windows ke Mac, atau ke mesin baru.

### Prasyarat di Mesin Baru
- Docker Desktop terinstal dan berjalan
- Git terinstal
- Node.js (untuk menjalankan mobile app)

### Langkah Migrasi

**Step 1: Clone atau copy proyek**
```powershell
# Jika menggunakan Git (direkomendasikan)
git clone <URL_REPO> inventory_mgm
cd inventory_mgm

# Atau copy folder proyek ke mesin baru
```

**Step 2: Buat file .env backend**
```powershell
# Salin dari contoh yang sudah ada
cp backend/.env.example backend/.env

# Edit .env — pastikan variabel ini sesuai:
# DB_CONNECTION=pgsql
# DB_HOST=db
# DB_PORT=5432
# DB_DATABASE=inventory_mgm
# DB_USERNAME=inventory_user
# DB_PASSWORD=secret
```

**Step 3: Build dan jalankan Docker**
```powershell
# Build image (hanya perlu pertama kali atau setelah Dockerfile berubah)
docker-compose build

# Jalankan semua kontainer
docker-compose up -d

# Tunggu ~30 detik agar database siap
```

**Step 4: Setup aplikasi Laravel**
```powershell
# Install PHP dependencies
docker-compose exec app composer install

# Generate APP_KEY (kunci enkripsi Laravel)
docker-compose exec app php artisan key:generate

# Jalankan migrasi database
docker-compose exec app php artisan migrate

# Isi data awal (admin, produk, supplier)
docker-compose exec app php artisan db:seed

# Generate Filament Admin user (jika diperlukan)
docker-compose exec app php artisan make:filament-user
```

**Step 5: Verifikasi**
```powershell
# Cek semua kontainer berjalan
docker-compose ps

# Buka browser: http://localhost:8000/admin
# Login: admin@inventory.test / password
```

---

## 🗄️ Manajemen Database

### Backup Database

```powershell
# Backup ke file SQL
docker-compose exec db pg_dump -U inventory_user inventory_mgm > backup_$(Get-Date -f yyyyMMdd).sql

# Verifikasi file backup
Get-Item backup_*.sql
```

### Restore Database

```powershell
# Restore dari file SQL (pastikan kontainer db berjalan)
Get-Content backup_20260709.sql | docker-compose exec -T db psql -U inventory_user -d inventory_mgm
```

### Akses Database Langsung (psql)

```powershell
# Masuk ke shell PostgreSQL
docker-compose exec db psql -U inventory_user -d inventory_mgm

# Contoh query di dalam psql:
# \dt          → lihat semua tabel
# \q           → keluar
# SELECT * FROM users LIMIT 5;
```

### pgAdmin 4 (GUI Database)

1. Buka pgAdmin 4
2. Klik kanan **Servers** → **Register** → **Server**
3. Isi konfigurasi:
   - **Name:** `Inventory Docker`
   - **Host:** `localhost` (atau `127.0.0.1`)
   - **Port:** `5432`
   - **Database:** `inventory_mgm`
   - **Username:** `inventory_user`
   - **Password:** `secret`

---

## 🔧 Perintah Artisan Berguna

> Jalankan dari **dalam kontainer** dengan prefix `docker-compose exec app`

```powershell
# Bersihkan cache semua
docker-compose exec app php artisan optimize:clear

# Buat user Filament baru interaktif
docker-compose exec app php artisan make:filament-user

# Cek route API yang terdaftar
docker-compose exec app php artisan route:list --path=api

# Reset semua data dan seed ulang
docker-compose exec app php artisan migrate:fresh --seed

# Lihat log Laravel terbaru
docker-compose exec app tail -f storage/logs/laravel.log
```

---

## 🛠️ Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `Port 8000 already in use` | Ubah port di `docker-compose.yml`: `"8001:80"` |
| `Port 5432 already in use` | PostgreSQL lokal aktif. Hentikan atau ubah port: `"5433:5432"` |
| Kontainer app tidak mau start | Jalankan `docker-compose logs app` untuk melihat error |
| Error `APP_KEY` | Jalankan `docker-compose exec app php artisan key:generate` |
| Database kosong setelah restart | Data ada di Docker Volume. Jalankan `docker-compose up -d` lagi (jangan `-v`) |
| Permission error di storage | `docker-compose exec app chmod -R 775 storage bootstrap/cache` |

---

## 📁 Struktur File Docker

```
inventory_mgm/
├── docker-compose.yml          ← Orkestrasi semua kontainer
├── Makefile                    ← Shortcut perintah (Linux/Mac/WSL)
├── docker-manage.ps1           ← Shortcut perintah (Windows PowerShell)
└── backend/
    ├── Dockerfile              ← Image PHP 8.4-FPM untuk kontainer app
    └── nginx/
        └── default.conf        ← Konfigurasi virtual host Nginx
```
