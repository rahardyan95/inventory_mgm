# 🔐 Kredensial Akun Demo

File ini berisi daftar akun demo yang otomatis dibuat oleh sistem saat inisialisasi database (menggunakan `DemoDataSeeder.php`). Gunakan akun-akun di bawah ini untuk menguji fitur aplikasi berdasarkan peran (Role-Based Access Control).

Semua akun di bawah ini menggunakan kata sandi (password) yang sama:
> **Password:** `password`

---

## 1. Akun Super Admin
Akun ini memiliki **akses penuh** ke seluruh sistem, termasuk membuat user baru, mengatur hak akses, dan melihat jejak audit (Audit Log).
- **Nama:** Super Admin
- **Email:** `admin@inventory.test`
- **Role:** `super_admin`

## 2. Akun Manajer
Akun ini memiliki akses **tertinggi untuk operasional gudang**. Dapat melihat dan mengekspor laporan, serta melakukan *approval* (persetujuan) atas transaksi inventaris.
- **Nama:** Budi Manajer
- **Email:** `manager@inventory.test`
- **Role:** `manager`

## 3. Akun Staf Gudang
Akun ini dikhususkan untuk pekerja operasional. Fitur dasbor disederhanakan dan hak akses dibatasi **hanya untuk input transaksi** (Barang Masuk / Keluar) dan melihat daftar barang. Tidak bisa mengakses menu laporan atau pengguna.
- **Nama:** Andi Gudang
- **Email:** `staff@inventory.test`
- **Role:** `staff`

---

> [!TIP]
> **Catatan Keamanan:** 
> Akun-akun ini hanya ditujukan untuk lingkungan *Development* (pengembangan) atau *Testing*. Jika aplikasi di-deploy ke lingkungan *Production* (seperti VPS), pastikan Anda **segera mengganti password** dari masing-masing akun tersebut, atau nonaktifkan akun demo dan buat akun asli baru!
