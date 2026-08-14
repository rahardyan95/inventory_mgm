# Product Requirements Document (PRD)

## Sistem Manajemen Inventaris (Inventory Management System)

| Field | Nilai |
|---|---|
| **Dokumen** | Product Requirements Document (PRD) |
| **Versi** | 2.0 |
| **Terakhir Diperbarui** | 2026-08-13 |
| **Status** | Disetujui |
| **Dokumen Terkait** | [BRD.md](./BRD.md) · [FRD.md](./FRD.md) · [AKUN_DEMO.md](./AKUN_DEMO.md) · [SETUP.md](./SETUP.md) |

---

## 1. Ringkasan Produk

Sistem manajemen inventaris berbasis web + mobile dengan **Role-Based Access Control (RBAC)** untuk tiga peran: **Super Admin**, **Manajer**, dan **Staf Gudang**. Web Dashboard dibangun dengan **Laravel 13 + Filament 5**; aplikasi mobile staf dibangun dengan **React Native (Expo)** dan berkomunikasi dengan API via **Laravel Sanctum**.

**Fitur unggulan:**
- Pemindaian barcode di mobile untuk transaksi inbound/outbound cepat.
- Grafik dan laporan real-time untuk pengambilan keputusan.
- Notifikasi stok menipis & barang kedaluwarsa.
- Jejak audit lengkap untuk akuntabilitas.

---

## 2. Target Pengguna & Peran

| Peran | Platform Utama | Tujuan Utama |
|---|---|---|
| **Super Admin** | Web | Manajemen pengguna, audit log, konfigurasi sistem |
| **Manajer** | Web | Monitoring stok, laporan & grafik, persetujuan transaksi |
| **Staf Gudang** | Mobile | Scan barcode, pencatatan barang masuk/keluar |

> Kredensial akun demo untuk pengujian tersedia di **[AKUN_DEMO.md](./AKUN_DEMO.md)**.

---

## 3. Fitur Web (Dashboard Filament)

| Fitur | Super Admin | Manajer | Staf |
|---|---|---|---|
| Dashboard ringkasan (widget statistik, grafik, tabel transaksi terkini) | ✅ | ✅ | ✅ (disederhanakan + grafik aktivitas sendiri) |
| Grafik Inbound vs Outbound (7 hari) & tren 3 tahun | ✅ | ✅ | ✅ (bar chart aktivitas sendiri, auto-refresh) |
| CRUD Produk (termasuk batch/lot, expiry date, barcode/SKU, reorder point) | ✅ | ✅ | ❌ (hanya lihat + buat + hapus) |
| CRUD Kategori & Supplier | ✅ | ✅ | ❌ (Supplier: hanya lihat) |
| Persetujuan (approval) stock opname | ✅ | ✅ | ❌ |
| Manajemen Pengguna (hanya Super Admin) | ✅ | ❌ | ❌ |
| Audit Log / Activity Log | ✅ | ❌ | ❌ |
| Laporan & ekspor PDF/Excel | ✅ | ✅ | ❌ |

### Alur Login Web
1. Pengguna membuka `http://localhost:8000/admin`.
2. Memasukkan email & password (atau klik tombol akun demo untuk auto-fill).
3. Sistem memvalidasi kredensial, memuat hak akses sesuai peran.
4. Diarahkan ke dashboard sesuai peran.

### Form Produk (Khusus Staf)
- **SKU & Barcode**: dibuat otomatis oleh sistem (read-only), keduanya **required** untuk mendukung fitur scan barcode di aplikasi mobile.
- **SKU**: menyesuaikan kategori yang dipilih (contoh: kategori *Elektronik* → `ELK-001`).
- **Satuan (unit)**: pilihan dropdown (pcs, box, pack, kg, liter, dll.) menyesuaikan kategori produk.
- **Gambar**: upload dari web; integrasi kamera aplikasi mobile menyusul setelah versi web selesai.

---

## 4. Fitur Mobile (React Native / Expo)

| Fitur | Deskripsi | Peran |
|---|---|---|
| **Otentikasi** | Login dengan email → token Sanctum | Semua |
| **Dashboard Dinamis** | Super Admin/Manajer: ringkasan stok, low-stock alert, grafik 7 hari. Staf: daftar inbound terbaru | Semua (konten per peran) |
| **Scanner Barcode** | Buka kamera, baca barcode produk, lalu otomatis lanjut ke form transaksi | Staf |
| **Transaksi Cepat** | Tambah/kurangi stok setelah barcode terbaca & dikonfirmasi (pilih supplier untuk inbound, validasi stok untuk outbound) | Staf |

---

## 5. Alur Transaksi Inti

### 5.0 Kode Nomor Referensi (Reference Number)

Setiap transaksi memiliki **Nomor Referensi** unik yang dibuat otomatis oleh sistem dengan format `{PREFIX}-{YYYYMMDD}-{NomorUrut 3 digit}`. Penjelasan prefix:

| Prefix | Arti | Jenis Transaksi |
|---|---|---|
| `INB` | **Inbound** — barang masuk ke gudang | Barang Masuk |
| `OUT` | **Outbound** — barang keluar dari gudang | Barang Keluar |
| `ADJ` | **Adjustment** — penyesuaian stok hasil opname fisik | Penyesuaian Stok |

> Contoh: `INB-20260813-001` = transaksi Barang Masuk tanggal 13 Agustus 2026 urutan ke-1 pada hari itu. Nomor referensi **tidak dapat diubah manual** (dibuat otomatis oleh sistem) untuk menjamin integritas audit.

### 5.1 Barang Masuk (Inbound)
1. Staf memindai barcode barang di mobile.
2. Mengisi jumlah, memilih supplier, mencatat nomor batch & tanggal kedaluwarsa.
3. Sistem menambah stok otomatis dan mencatat log transaksi.

### 5.2 Barang Keluar (Outbound)
1. Staf memindai barcode barang yang akan keluar.
2. Sistem memvalidasi ketersediaan stok (metode FEFO/FIFO).
3. Sistem mengurangi stok otomatis dan mencatat log transaksi.

### 5.3 Stock Opname (Penyesuaian Fisik)
1. Staf menghitung stok fisik di gudang dan mencatat selisih.
2. Penyesuaian memerlukan **persetujuan Manajer**.
3. Setelah disetujui, stok sistem diperbarui.

---

## 6. Non-Fungsional (Ringkasan)

| Aspek | Persyaratan |
|---|---|
| **Keamanan** | RBAC, token Sanctum, proteksi brute-force, audit trail |
| **Performa** | Mendukung volume transaksi tinggi; ekspor besar via background job |
| **Ketersediaan** | Docker dengan restart otomatis; backup DB terjadwal |
| **Kompatibilitas** | Android (Expo Go / APK), iOS (Expo Go / IPA), Windows & macOS |

---

## 7. Batasan (Constraints)

- Ekspor PDF/Excel besar dijalankan **asinkronus** (background job).
- iOS build hanya dapat dilakukan di macOS (Xcode).
- Fase 1 **tidak** mencakup integrasi POS pihak ketiga dan modul akuntansi penuh.

---

## 8. Kriteria Penerimaan (Acceptance Criteria)

- [x] Login per peran berhasil dengan kredensial demo.
- [x] Staf dapat melakukan transaksi inbound/outbound via mobile (stok bertambah/berkurang).
- [x] Manajer/Super Admin melihat grafik & laporan; Staf tidak.
- [x] Super Admin dapat membuat akun staf baru.
- [x] Semua transaksi tercatat dalam audit log.

---

## 9. Referensi

- [BRD.md](./BRD.md) — kebutuhan & tujuan bisnis
- [FRD.md](./FRD.md) — spesifikasi fungsional detail
- [AKUN_DEMO.md](./AKUN_DEMO.md) — kredensial akun demo
- [SETUP.md](./SETUP.md) — panduan instalasi lingkungan pengembangan
- [DOCKER.md](./DOCKER.md) — panduan operasi Docker
