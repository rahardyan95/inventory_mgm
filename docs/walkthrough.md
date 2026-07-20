# Walkthrough: Custom Dasbor Inventaris (Filament)

Berdasarkan *Implementation Plan* yang Anda setujui, saya telah merombak total tampilan halaman muka (Dasbor) Web Filament agar lebih cantik, fungsional, dan sesuai dengan desain tema **Admindek** yang Anda berikan.

Berikut rincian dari fitur yang telah ditambahkan:

## 1. Widget Statistik Berwarna (Grid Atas)
- Saya telah membuat **InventoryStatsWidget** yang menggantikan *widget default* bawaan Filament.
- Terdapat 4 kotak ringkasan utama yang memiliki warna solid mencolok layaknya referensi Anda:
  - **Biru:** Jumlah Total Produk (Semua jenis barang).
  - **Oranye (Peringatan):** Produk dengan stok menipis (di bawah batas minimum) – fitur esensial untuk manajemen inventaris.
  - **Hijau:** Total *Inbound* (Barang Masuk) pada bulan berjalan.
  - **Cyan:** Total *Outbound* (Barang Keluar) pada bulan berjalan.

## 2. Grafik Analitik Dinamis (Tengah)
- **Baris 2 Kolom:** Saya mengatur lebar halaman dasbor menjadi 3 kolom secara spesifik. Grafik Tren Inbound (3 Tahun Terakhir) telah diperlebar sehingga memakan **2 kolom**, menciptakan *focal point* yang mirip dengan grafik "Real-time Analytics" pada contoh.
- **Doughnut Chart (1 Kolom):** Di sebelahnya, saya menambahkan grafik bulat (Doughnut Chart) bernama **StockDistributionChart** yang akan menampilkan perbandingan total stok barang berdasarkan masing-masing kategori (sebagai pengganti "Device Analytics").

## 3. Tabel Transaksi Terkini (Bawah)
- Saya menambahkan **RecentTransactionsWidget** di baris paling bawah.
- *Widget* ini menampilkan tabel berisikan **5 transaksi terakhir**, lengkap dengan status (*badge* warna), jenis transaksi (*inbound, outbound, adjustment*), dan informasi staf yang bertanggung jawab.

## 4. Pembatasan Hak Akses (Role-Based)
Saya menyesuaikan jumlah informasi yang ditampilkan berdasarkan peran pengguna (*role*):
- **Super Admin & Manajer:** Akan melihat 4 widget statistik (lengkap dengan angka *Inbound/Outbound*), kedua grafik besar (Line & Doughnut), dan daftar semua transaksi terkini.
- **Staf:** Agar tidak membingungkan dan fokus pada operasional gudang, dasbor staf secara otomatis disederhanakan. Staf **hanya** akan melihat **2 widget statistik** (Total Produk & Peringatan Stok Menipis) dan **Tabel Transaksi Terkini** (yang difilter hanya untuk transaksi yang mereka buat sendiri). Grafik analitik tidak ditampilkan di akun staf.

---

> [!TIP]
> **Cara Menguji:** Silakan *refresh* halaman dasbor pada *browser* Anda sekarang untuk melihat tata letak yang baru. Pastikan juga mencoba masuk (login) menggunakan akun `staff@inventory.test` (password: `password`) untuk merasakan perbedaannya dengan dasbor Super Admin!
