# Rencana Migrasi Docker & Strategi Deployment VPS

Menjawab pertanyaan Anda: **Ya, bermigrasi menggunakan Docker sangat direkomendasikan.** 
Masalah perbedaan versi PHP (8.4 di Mac, 8.3 di Windows) yang baru saja kita alami adalah contoh klasik mengapa Docker sangat dibutuhkan. Docker akan mengeliminasi isu "jalan di mesin saya, tapi error di mesin lain".

## Strategi & Efisiensi Cross-Platform (Windows ↔ Mac)

Untuk mencapai efisiensi maksimal saat berpindah mesin, kita akan menggunakan pendekatan ini:

1. **Backend di-Dockerize (Laravel & Database):** 
   Seluruh backend (PHP, Composer, Nginx/Web Server, dan Database) akan dibungkus dalam kontainer. Di Windows atau Mac, Anda hanya perlu menjalankan `docker-compose up -d` tanpa perlu menginstal PHP atau Composer di mesin lokal.
2. **Mobile App tetap di Host (Expo):**
   Aplikasi mobile (React Native/Expo) **tidak** disarankan untuk dimasukkan ke dalam Docker. Expo membutuhkan akses langsung ke jaringan lokal untuk berkomunikasi dengan HP fisik (Expo Go) atau Emulator/Simulator. Menjalankan Expo di dalam Docker (terutama di Mac/Windows) sering menimbulkan masalah *network bridging*. Jadi, Mobile akan tetap berjalan di host, tapi menembak API ke Backend yang ada di Docker.

## Rencana Implementasi (Proposed Changes)

Saya akan membuat setup arsitektur Docker yang siap untuk *Development* (Lokal) dan mudah ditransisikan ke *Production* (VPS).

### 1. Persiapan Struktur Docker
#### [NEW] `backend/Dockerfile`
- Membuat image kustom berbasis `php:8.4-fpm-alpine`.
- Akan menginstal otomatis dependensi sistem (curl, zip, mbstring, pdo_sqlite, pdo_pgsql).
- Mengintegrasikan Node.js di dalam kontainer untuk *build* Vite asset.

#### [NEW] `docker-compose.yml` (di root proyek)
- **Service `app`:** Menjalankan Laravel PHP-FPM.
- **Service `web`:** Menjalankan Nginx untuk me-routing HTTP request ke Laravel.
- **Service `db`:** Menggunakan PostgreSQL (Sangat disarankan untuk beralih dari SQLite sebelum naik ke VPS demi performa *Enterprise*).

#### [NEW] `backend/nginx/default.conf`
- Konfigurasi Nginx standar untuk melayani aplikasi Laravel.

### 2. Transisi Database (Opsional namun Penting)
Saat ini proyek menggunakan **SQLite**. Untuk lingkungan Enterprise di VPS, SQLite akan menghadapi isu *database lock* jika ada banyak transaksi Inbound/Outbound bersamaan.
- Saya akan menyiapkan service `postgres` di `docker-compose.yml`.
- Konfigurasi `.env` akan disiapkan untuk menggunakan PostgreSQL.

### 3. Skrip Bantuan (Helper)
#### [NEW] `Makefile` (atau skrip `.bat` / `.sh`)
- Membuat perintah singkat agar Anda tidak perlu mengetik perintah Docker yang panjang.
- Contoh: `make up`, `make down`, `make migrate`.

## Persiapan Menuju VPS (Production)

Setup Docker ini adalah langkah pertama yang krusial sebelum naik ke VPS:
1. **Identik:** Lingkungan di laptop Anda (Windows/Mac) akan 100% identik dengan server VPS (karena menggunakan *image* kontainer yang sama).
2. **Deployment Mudah:** Di VPS nanti, Anda hanya perlu melakukan `git pull` dan `docker-compose up -d --build`. Tidak perlu repot menginstal dan mengkonfigurasi PHP 8.4 atau Nginx secara manual di VPS.

---

> [!IMPORTANT]
> **User Review Required**
> 
> Sebelum saya mengeksekusi pembuatan file-file Docker ini, mohon konfirmasi:
> 1. Apakah Anda setuju untuk **beralih dari SQLite ke PostgreSQL** di dalam Docker? (Ini sangat baik untuk persiapan VPS).
> 2. Apakah Anda siap untuk beralih menggunakan *workflow* Docker untuk backend mulai sekarang?
> 
> Jika Anda setuju, silakan klik **Proceed/Lanjutkan** dan saya akan membuatkan konfigurasi Docker-nya sekarang.
