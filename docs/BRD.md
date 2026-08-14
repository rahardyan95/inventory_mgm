# Business Requirements Document (BRD)

## Sistem Manajemen Inventaris Enterprise

| Field | Nilai |
|---|---|
| **Nama Proyek** | Enterprise Inventory Management System |
| **Dokumen** | Business Requirements Document (BRD) |
| **Versi** | 2.0 |
| **Terakhir Diperbarui** | 2026-08-13 |
| **Status** | Disetujui |
| **Dokumen Terkait** | [PRD.md](./PRD.md) · [FRD.md](./FRD.md) · [README.md](./README.md) |

---

## 1. Ringkasan Eksekutif

Dokumen ini menguraikan kebutuhan bisnis untuk pengembangan **Sistem Manajemen Inventaris** yang ditujukan bagi bisnis berskala menengah ke atas (enterprise). Sistem ini menggantikan proses manual atau sistem legacy yang kurang aman dan efisien, guna memastikan pengelolaan stok barang dan hubungan supplier berjalan secara **real-time, aman, akurat, dan skalabel**.

Sistem terdiri dari dua permukaan akses utama:
1. **Web Dashboard (Admin)** — Laravel 13 + Filament 5, untuk Super Admin & Manajer.
2. **Mobile App (Staf Gudang)** — React Native (Expo), untuk pemindaian barcode dan transaksi gudang.

---

## 2. Tujuan Bisnis (Business Objectives)

| # | Tujuan | KPI / Ukuran Keberhasilan |
|---|---|---|
| 1 | **Efisiensi Operasional** — Otomatisasi pencatatan masuk/keluar barang untuk meminimalkan *human error*. | Pengurangan waktu pencatatan transaksi ≥ 50% dibanding proses manual. |
| 2 | **Akurasi Data** — Ketersediaan stok di sistem sesuai kondisi fisik gudang. | Tingkat kesesuaian stok sistem vs fisik ≥ 95% (diukur saat opname). |
| 3 | **Transparansi & Akuntabilitas** — Pelaporan yang dapat dipertanggungjawabkan untuk audit. | 100% transaksi tercatat lengkap (siapa, kapan, berapa, dari mana). |
| 4 | **Keamanan Skala Enterprise** — Mencegah kebocoran data dan manipulasi stok. | Tidak ada insiden keamanan kritis; akses dibatasi berbasis peran (RBAC). |
| 5 | **Kesinambungan Bisnis** — *High Availability* dan prosedur pemulihan bencana (*Disaster Recovery*). | *Recovery Time Objective* (RTO) ≤ 4 jam; *Recovery Point Objective* (RPO) ≤ 24 jam. |

---

## 3. Target Pengguna (Target Audience)

**Bisnis skala menengah hingga atas (enterprise)** dengan karakteristik:

- Volume transaksi inventaris **tinggi** (≥ 1 juta SKU ditargetkan).
- Struktur organisasi yang membutuhkan **segregasi tugas** antar peran.
- Membutuhkan pelacakan batch/lot dan tanggal kedaluwarsa (industri FMCG, farmasi, ritel).

### Persona Utama

| Persona | Deskripsi | Kebutuhan Utama |
|---|---|---|
| **Super Admin** | Administrator IT / sistem | Manajemen pengguna, audit log, konfigurasi sistem |
| **Manajer** | Pimpinan operasional gudang | Laporan, grafik, persetujuan transaksi, keputusan stok |
| **Staf Gudang** | Petugas operasional lapangan | Scan barcode, input barang masuk/keluar, stock opname |

---

## 4. Ruang Lingkup (Scope)

### 4.1 In-Scope

- Manajemen data barang & varian (termasuk pelacakan **Lot/Batch** & **Tanggal Kedaluwarsa**).
- Manajemen supplier.
- Transaksi inventaris: **Barang Masuk (Inbound)**, **Barang Keluar (Outbound)**, **Stock Opname** (penyesuaian fisik).
- Pemindaian **Barcode / QR Code** (mobile).
- **Notifikasi stok menipis** (low-stock alert) & peringatan kedaluwarsa (expiry alert).
- **Pelaporan & ekspor** ke PDF dan Excel (asinkronus untuk data besar).
- Autentikasi berbasis peran (RBAC) + audit trail lengkap.
- Web Dashboard (Filament) + Aplikasi Mobile (Expo).

### 4.2 Out-of-Scope (Fase 1)

- Integrasi langsung dengan mesin kasir (POS) pihak ketiga.
- Modul akuntansi penuh (general ledger, invoicing).
- E-commerce / katalog publik.

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)

| Kategori | Kebutuhan |
|---|---|
| **Keamanan** | RBAC (Spatie Permission), enkripsi password, proteksi SQL Injection / XSS / CSRF, otentikasi token (Sanctum) untuk API |
| **Jejak Audit** | Setiap perubahan data (stok, supplier, pengguna, login) tercatat: siapa, kapan, dari mana (Activity Log) |
| **Skalabilitas** | Mampu memproses transaksi volume besar (> 1 juta SKU) tanpa penurunan responsivitas signifikan |
| **Ketersediaan** | High Availability dengan *restart policy* otomatis pada kontainer; backup database terjadwal |
| **Performa** | API responsif (paging, indeks database); ekspor data besar dijalankan sebagai background job |
| **Portabilitas** | Lingkungan pengembangan identik lintas OS (Windows/macOS/Linux) via Docker |

---

## 6. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Data stok tidak akurat (selisih sistem vs fisik) | Keputusan bisnis salah, stockout/overstock | Stock opname berkala + validasi stok pada outbound |
| Serangan keamanan (injection, brute force) | Kebocoran data, manipulasi stok | Enkripsi, sanitasi input, proteksi brute-force, RBAC ketat |
| Ketergantungan satu perangkat (scan barcode) | Operasional terhenti | Backup metode input manual via web dashboard |
| Kehilangan data (bencana/hardware) | Bisnis terhenti | Volume Docker + backup DB terjadwal + prosedur restore |
| Perbedaan lingkungan dev vs production | Bug yang tidak terdeteksi | Kontainerisasi Docker (environment identik) |

---

## 7. Asumsi & Dependensi

- Infrastruktur: Docker (development & production), PostgreSQL 16.
- Lisensi: Laravel (MIT), Filament, React Native/Expo (gratis untuk penggunaan ini).
- Tim memiliki pengetahuan Laravel, Filament, dan React Native.

---

## 8. Persetujuan (Sign-off)

| Peran | Nama | Tanggal | Tanda Tangan |
|---|---|---|---|
| Business Owner | — | — | |
| Product Manager | — | — | |
| Lead Developer | — | — | |
