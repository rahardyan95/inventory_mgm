# Panduan Menghubungkan PostgreSQL ke pgAdmin 4

Untuk melakukan *monitoring* dan manajemen database secara visual, Anda dapat menggunakan aplikasi **pgAdmin 4** (seperti yang terlihat pada *screenshot* Anda).

Karena database kita sekarang berjalan di dalam **Docker (PostgreSQL 16)**, berikut adalah langkah-langkah untuk menghubungkannya:

## Kredensial Database
Berdasarkan file `docker-compose.yml` dan `.env`, kredensial database Anda adalah:
- **Host name / address:** `localhost` atau `127.0.0.1`
- **Port:** `5432`
- **Maintenance database:** `inventory_mgm`
- **Username:** `inventory_user`
- **Password:** `secret`

---

## Langkah-langkah Koneksi di pgAdmin 4

1. Buka aplikasi **pgAdmin 4**.
2. Pada halaman awal (seperti *screenshot*), klik icon **Add New Server** di menu *Quick Links*.
3. Akan muncul *pop-up* "Register - Server".
4. Di tab **General**:
   - **Name:** Bebas (misal: `Inventory Local Docker`)
5. Pindah ke tab **Connection**:
   - **Host name/address:** Ketik `localhost` (atau `127.0.0.1`)
   - **Port:** `5432` (biarkan default)
   - **Maintenance database:** Ketik `inventory_mgm`
   - **Username:** Ketik `inventory_user`
   - **Password:** Ketik `secret`
   - **Save password:** Centang (*opsional, agar tidak perlu login terus*)
6. Klik tombol **Save**.

## Cara Melihat Data Tabel

1. Setelah tersimpan, perhatikan panel sebelah kiri (**Browser/Servers**).
2. Klik ikon `>` di sebelah **Servers** -> **Inventory Local Docker** (atau nama yang Anda buat).
3. Buka **Databases** -> **inventory_mgm** -> **Schemas** -> **public** -> **Tables**.
4. Anda akan melihat daftar tabel seperti `users`, `products`, `transactions`, dll.
5. Untuk melihat data di dalam tabel, klik kanan pada tabel yang diinginkan (misalnya `users`), lalu pilih **View/Edit Data** -> **All Rows**.

> [!TIP]
> Jika Anda mengalami *connection refused*, pastikan status *container* Docker untuk `inventory_db` sedang menyala. Anda bisa menyalakannya menggunakan skrip `.\docker-manage.ps1 up`.
