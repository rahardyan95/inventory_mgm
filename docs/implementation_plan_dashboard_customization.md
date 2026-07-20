# Implementation Plan: Kustomisasi Dashboard Filament (Inventory Management)

## Tujuan
Menyesuaikan tampilan dan fungsionalitas Dasbor Web (Filament Admin) agar menyerupai referensi desain (tema gelap dengan widget berwarna, grafik dinamis, dan tabel) serta mengelompokkan fiturnya berdasarkan hak akses (Super Admin, Manager, Staff) khusus untuk kebutuhan manajemen inventaris.

## User Review Required
> [!IMPORTANT]
> Karena Filament memiliki kerangka layout bawaan, saya akan menggunakan sistem **Grid dan Widget Filament** untuk menyusun tata letaknya agar semirip mungkin dengan referensi gambar Anda. Harap tinjau rencana tata letak di bawah ini dan berikan persetujuan Anda.

## Rencana Tata Letak & Widget

Saya akan membuat halaman `Dashboard` kustom yang menampung susunan *widget* berikut:

### 1. Stats Overview Widget (Baris Atas)
Menampilkan 4 kartu ringkasan dengan warna latar belakang solid (Biru, Hijau, Oranye, Cyan) meniru tampilan "Analytics" pada gambar.
- **Total Produk** (Biru)
- **Stok Menipis** (Merah/Oranye) - *Penting untuk inventory*
- **Total Inbound Bulan Ini** (Hijau)
- **Total Outbound Bulan Ini** (Cyan)

### 2. Analytics Charts (Baris Tengah)
- **Grafik Tren Transaksi (Line Chart):** Mengambil ruang 2 kolom. Mirip dengan "Real-time Analytics". Menampilkan tren Inbound & Outbound.
- **Distribusi Kategori (Doughnut Chart):** Mengambil ruang 1 kolom. Mirip dengan "Device Analytics". Menampilkan persentase stok berdasarkan kategori.

### 3. Detail & Aktivitas (Baris Bawah)
- **Tabel Transaksi Terkini (Table Widget):** Mengambil ruang 2/3 kolom. Menampilkan daftar transaksi Inbound/Outbound terbaru beserta statusnya.
- **Aktivitas Terbaru (Feed/List Widget):** Mengambil ruang 1/3 kolom. Menampilkan riwayat aksi *user* terakhir.

## Pembatasan Akses (Role-Based)
- **Super Admin & Manager:** Akan melihat **seluruh widget** (Stats, Charts, Table, Activity).
- **Staff:** Hanya akan melihat **Stats (Total Produk & Stok Menipis)** dan **Tabel Transaksi Terkini** (hanya transaksi milik mereka atau transaksi gudang harian). Grafik analitik tidak akan ditampilkan agar tampilan lebih ringkas dan fokus pada operasional.

## Penyesuaian Tema & Warna
- **AdminPanelProvider:** Akan dimodifikasi untuk menggunakan palet warna khusus, mengunci mode ke *Dark Theme*, dan mendaftarkan halaman Dasbor baru yang menggunakan sistem Grid kustom Filament (kolom 1, 2, atau 3).

## Verification Plan
1. Mengubah `AdminPanelProvider` dan membuat halaman Dasbor kustom.
2. Membuat dan menyusun *class* widget (Stats, Line Chart, Pie Chart, Table).
3. Menguji *login* dengan akun Manager dan Staff untuk memastikan fungsionalitas dan visibilitas *widget* berjalan sesuai batasan peran.
