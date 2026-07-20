# Product Requirements Document (PRD)

## Sistem Manajemen Inventaris (Inventory Management System)

### Akun Demo (Untuk Pengujian)

Aplikasi ini memiliki sistem otentikasi berbasis peran (Role-Based Access Control) dengan tiga peran utama: Super Admin, Manajer, dan Staf. Berikut adalah akun demo yang dapat digunakan untuk masuk ke dalam sistem:

#### 1. Super Admin
- **Email:** `admin@inventory.test`
- **Password:** `password`
- **Fitur Utama:** Memiliki akses penuh ke seluruh sistem, termasuk manajemen pengguna, pengaturan gudang utama, laporan keseluruhan, dan metrik (grafik) performa inventaris.

#### 2. Manajer (Manager)
- **Email:** `manager@inventory.test`
- **Password:** `password`
- **Fitur Utama:** Memantau pergerakan stok, melihat laporan grafik (Inbound vs Outbound), menambahkan kategori atau supplier baru, serta membuat keputusan berdasarkan *Low-Stock Alerts*.

#### 3. Staf Gudang (Staff)
- **Email:** `staff@inventory.test`
- **Password:** `password`
- **Fitur Utama:** Melakukan pemindaian barcode (Scanner), mencatat barang masuk (Inbound) dan barang keluar (Outbound). Pada dashboard staf, mereka difokuskan pada daftar transaksi Inbound terakhir yang berhasil mereka catat.

---

### Fitur Mobile yang Tersedia
- **Otentikasi:** Login menggunakan email dan token otentikasi (Sanctum).
- **Dashboard Dinamis:** 
  - **Super Admin/Manajer:** Menampilkan ringkasan stok, peringatan stok rendah, serta grafik transaksi 7 hari terakhir.
  - **Staf:** Menampilkan daftar aktivitas pemasukan barang terakhir.
- **Scanner Barcode:** Membuka kamera dan membaca barcode produk untuk diproses di transaksi.
- **Transaksi Cepat:** Mengurangi atau menambah stok langsung setelah barcode terbaca dan dikonfirmasi.

---

### Panduan Setup & Instalasi
Untuk panduan lengkap cara menyiapkan lingkungan pengembangan (Windows 11 & macOS), silakan lihat **[SETUP.md](SETUP.md)**.
