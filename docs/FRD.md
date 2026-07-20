# Functional Requirements Document (FRD)
## Sistem Manajemen Inventaris Enterprise

### 1. Peran Pengguna (User Roles) & Hak Akses
1. **Super Admin:** Memiliki akses ke semua modul, termasuk manajemen pengguna, pengaturan sistem, dan log audit.
2. **Manajer Gudang (Manager):** Memiliki akses untuk melihat semua laporan, menyetujui transaksi (approval), dan mengelola master data barang & supplier.
3. **Staf Gudang (Staff):** Hanya dapat melakukan input barang masuk/keluar, stock opname fisik, dan melihat sisa stok operasional.

### 2. Modul Fungsional & Alur Sistem (System Flows)

#### A. Manajemen Master Data
- **Stok Barang:** 
  - CRUD (Create, Read, Update, Delete) data barang.
  - Atribut Tambahan (Enterprise): Nomor Batch/Lot, Tanggal Kedaluwarsa (Expiry Date), Barcode / SKU, Minimum Reorder Point.
- **Supplier:**
  - CRUD data supplier (Kontak, Alamat, Status Aktif).

#### B. Transaksi Inventaris (Alur Barang Masuk & Keluar)
- **Barang Masuk (Inbound):**
  1. Staf memindai (*scan*) Barcode barang.
  2. Memasukkan jumlah, memilih supplier, mencatat Nomor Batch dan Tanggal Kedaluwarsa.
  3. Sistem menambah stok barang secara otomatis dan mencatat log.
- **Barang Keluar (Outbound):**
  1. Staf memindai Barcode barang yang akan keluar.
  2. Sistem memvalidasi ketersediaan stok berdasarkan metode FEFO (First Expired, First Out) atau FIFO (First In, First Out).
  3. Sistem mengurangi stok otomatis.
- **Stock Opname (Physical Count):**
  - Modul untuk penyesuaian (adjustment) stok antara sistem dan fisik di gudang. Membutuhkan persetujuan (approval) Manajer.

#### C. Pemberitahuan (Notifications) & Alerts
- **Low-Stock Alert:** Notifikasi ke dashboard Manajer saat stok menyentuh batas *Minimum Reorder Point*.
- **Expiry Alert:** Notifikasi barang yang akan kedaluwarsa dalam 30, 60, atau 90 hari.

#### D. Laporan & Export Data
- **Laporan Stok Real-Time & Riwayat Transaksi.**
- **Export Data (Background Job):** Ekspor PDF dan Excel (Asinkronus untuk data besar).

### 3. Modul Keamanan (Security Features)
- **Otentikasi & Otorisasi:** Menggunakan login standar yang aman dengan middleware perlindungan brute-force.
- **Audit Logs:** Menampilkan seluruh riwayat aktivitas (*Activity Log*) untuk setiap aksi penting seperti login, perubahan stok, dan modifikasi master data.
