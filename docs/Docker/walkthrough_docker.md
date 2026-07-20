# Walkthrough: Migrasi Docker & Transisi PostgreSQL

Implementasi *Dockerization* backend dan migrasi database ke PostgreSQL telah berhasil! Ini adalah langkah krusial untuk membuat lingkungan *development* 100% identik di mesin apa pun (Windows, Mac, Linux) dan persiapan langsung (Plug & Play) untuk server *production* (VPS).

---

## 1. Arsitektur Docker yang Telah Dibangun

Saya telah menyusun konfigurasi 3-Tier Architecture di dalam `docker-compose.yml`:

| Service | Deskripsi | Image/Platform |
|---------|-------------|----------------|
| **db** | Server Database Enterprise, menggantikan SQLite. Berjalan di port 5432. Data disimpan permanen di volume `inventory_db_data`. | `postgres:16-alpine` |
| **app** | Backend framework (Laravel 13). Menggunakan image PHP-FPM khusus. Memiliki akses ke kode PHP yang ada di `backend/`. | `php:8.4-fpm-alpine` (Custom Build) |
| **web** | Web server ringan & super cepat untuk melayani *routing* HTTP. Mengekspos port 80 ke **localhost:8000**. | `nginx:alpine` |

> [!TIP]
> **Mobile App (React Native/Expo)** tetap berjalan di luar Docker (di terminal Windows/Mac) agar tetap mudah berkomunikasi dengan Emulator HP/Expo Go.

## 2. Transisi Database: SQLite → PostgreSQL

1. **Ubah Konfigurasi:** `.env` aplikasi sekarang menunjuk ke kontainer `db` (`DB_CONNECTION=pgsql`).
2. **Setup Sukses:** Perintah migrasi sukses dijalankan di dalam kontainer:
   ```bash
   docker-compose exec app php artisan migrate:fresh --seed
   ```
   *Seluruh skema database dan akun demo (Super Admin, Manager, Staff) berhasil dimasukkan ke PostgreSQL!*

## 3. Toolchain & Helper Scripts (Efisiensi Cross-Platform)

Agar Anda **tidak perlu repot menghafal perintah Docker**, saya telah membuat *helper script* untuk Windows & Mac:

### 🪟 Pengguna Windows (`docker-manage.ps1`)
Buka PowerShell di root proyek dan jalankan:
- `.\docker-manage.ps1 up` (Menyalakan aplikasi backend)
- `.\docker-manage.ps1 down` (Mematikan aplikasi)
- `.\docker-manage.ps1 bash` (Masuk ke terminal *container* backend)
- `.\docker-manage.ps1 fresh` (Reset database ke awal)

### 🍎 Pengguna Mac / Linux (`Makefile`)
Buka Terminal di root proyek dan jalankan:
- `make up`
- `make down`
- `make bash`
- `make fresh`

---

## Cara Mencoba Aplikasi Sekarang

1. **Backend & Dashboard Admin** sudah berjalan di: `http://localhost:8000/admin` (sekarang disajikan oleh Nginx + PostgreSQL).
2. Login seperti biasa menggunakan `admin@inventory.test` dan `password`.
3. Untuk Mobile App, jalankan `npm start` di folder `mobile` seperti biasa!

> [!NOTE]
> Jika nanti Anda ingin melakukan deploy ke **VPS**, Anda hanya perlu menyalin folder proyek (atau pull dari Git), lalu jalankan `docker-compose up -d --build`. Seluruh server, dependensi, dan database akan langsung online dalam hitungan detik.
