# Functional Requirements Document (FRD)

## Sistem Manajemen Inventaris Enterprise

| Field | Nilai |
|---|---|
| **Dokumen** | Functional Requirements Document (FRD) |
| **Versi** | 2.0 |
| **Terakhir Diperbarui** | 2026-08-13 |
| **Status** | Disetujui |
| **Dokumen Terkait** | [BRD.md](./BRD.md) · [PRD.md](./PRD.md) |

**Konvensi ID:** Setiap kebutuhan fungsional memiliki ID unik `FR-XX` dengan prioritas:
- **P0** = Wajib (blocking) · **P1** = Penting · **P2** = Peningkatan (nice-to-have)

---

## 1. Matriks Peran & Hak Akses

| Modul / Aksi | Super Admin | Manajer | Staf Gudang |
|---|---|---|---|
| Dashboard ringkasan | ✅ | ✅ | ✅ (disederhanakan + grafik aktivitas sendiri) |
| Grafik analitik (line/doughnut, 7 hari & 3 tahun) | ✅ | ✅ | ✅ (bar chart aktivitas sendiri, auto-refresh 30s) |
| Produk — CRUD | ✅ | ✅ | ❌ (hanya lihat + buat + hapus, **tidak bisa edit**) |
| Kategori & Supplier — CRUD | ✅ | ✅ | ❌ (Supplier: **hanya lihat**) |
| Transaksi Inbound/Outbound — buat | ✅ | ✅ | ✅ |
| Stock Opname — buat | ✅ | ✅ | ✅ |
| Stock Opname — **persetujuan (approval)** | ✅ | ✅ | ❌ |
| Laporan & ekspor PDF/Excel | ✅ | ✅ | ❌ |
| Manajemen pengguna (User Resource) | ✅ | ❌ | ❌ |
| Audit Log / Activity Log | ✅ | ❌ | ❌ |

---

## 2. Modul Fungsional

### A. Master Data

| ID | Kebutuhan | Prioritas | Kriteria Penerimaan |
|---|---|---|---|
| FR-01 | **Produk:** CRUD lengkap dengan atribut Nama, SKU/Barcode, Kategori, Harga, Minimum Reorder Point, Status Aktif | P0 | Admin/Manajer dapat membuat, mengubah, menonaktifkan, dan menghapus produk; validasi SKU unik. **Staff**: dapat membuat & menghapus, **tidak dapat mengedit** |
| FR-01a | **SKU & Barcode Otomatis:** SKU mengikuti kategori (`{PREFIX}-###`); barcode 13 digit (EAN-13 style) unik; keduanya read-only & required | P0 | Membuat produk tanpa mengisi SKU/Barcode tetap berhasil; keduanya ter-generate otomatis & dapat dipindai di mobile |
| FR-01b | **Satuan Produk:** pilihan dropdown (pcs, box, pack, kg, liter, dll.) menyesuaikan kategori | P1 | Form unit berupa Select; nilai default `pcs` |
| FR-02 | **Varian & Lot:** Pelacakan Nomor Batch/Lot dan Tanggal Kedaluwarsa per stok masuk | P0 | Setiap inbound dapat menyertakan lot + expiry date; data tersimpan dan dapat difilter |
| FR-03 | **Kategori:** CRUD untuk pengelompokan produk | P1 | Kategori terdaftar; produk terkait mengikuti struktur kategori |
| FR-04 | **Supplier:** CRUD (kontak, alamat, status aktif) | P0 | Admin/Manajer dapat mengelola data supplier; **Staff hanya dapat melihat**. Form mengikuti standar warehouse: nama perusahaan, kontak person, email, telepon, NPWP, ketentuan pembayaran, alamat, catatan, status aktif |

### B. Transaksi Inventaris

| ID | Kebutuhan | Prioritas | Kriteria Penerimaan |
|---|---|---|---|
| FR-05 | **Inbound (Barang Masuk):** scan barcode → input jumlah + supplier + lot + expiry → stok bertambah otomatis | P0 | Setelah submit, `current_stock` bertambah sesuai jumlah; log transaksi tercatat |
| FR-06 | **Outbound (Barang Keluar):** scan barcode → validasi stok (FEFO/FIFO) → stok berkurang otomatis | P0 | Stok tidak dapat menjadi negatif; validasi gagal jika stok tidak cukup |
| FR-07 | **Stock Opname:** pencatatan selisih fisik vs sistem + alur approval Manajer | P0 | Adjustment hanya berlaku setelah disetujui; riwayat penyesuaian tercatat |
| FR-08 | **Riwayat Transaksi:** daftar lengkap dengan filter (tanggal, tipe, produk, staf) | P0 | Data transaksi dapat dicari, difilter (jenis & status), dan dipaginasi |
| FR-08a | **Nomor Referensi Otomatis:** dibuat sistem, tidak bisa diisi manual | P0 | Format `{PREFIX}-YYYYMMDD-###`. Prefix: `INB` = Inbound/Barang Masuk, `OUT` = Outbound/Barang Keluar, `ADJ` = Adjustment/Penyesuaian Stok. Read-only pada form |
| FR-08b | **Anti-Fraud Form:** "Dibuat Oleh" & "Disetujui Oleh" otomatis mengikuti akun login; Staff tidak bisa mengisi status/approval | P0 | Staff: status selalu `pending`, `approved_by`/`approved_at` null; Manajer/Super Admin: approval atas nama akun sendiri. "Disetujui Oleh" menampilkan format **Nama - Role Indonesia** |
| FR-08c | **Field Immutable (edit):** Jenis Transaksi, Nomor Referensi, Tanggal Transaksi, & Tanggal Persetujuan tidak dapat diubah setelah transaksi dibuat 1x (semua role) | P0 | Saat edit: field `disabled`; nilai asli dipertahankan di server (`mutateFormDataBeforeSave`) |
| FR-08d | **Tanggal Persetujuan:** menggunakan DatePicker seragam `DD/MM/YYYY` (tanpa field waktu); presisi waktu tetap tersimpan di database | P0 | Form pakai `DatePicker`; kolom `approved_at` (timestamp) tetap mencatat waktu presisi |
| FR-08e | **RBAC Edit Staff:** Staff dapat membuat & mengedit transaksi miliknya sendiri yang berstatus `pending`; **tidak dapat menghapus**; transaksi yang sudah `approved` oleh Manajer/Super Admin **tidak dapat diedit** | P0 | `canEdit`: admin/manager true; staff hanya jika `user_id` miliknya & `status != approved`; `canDelete` hanya admin/manager |
| FR-17 | **Manajemen Roles (Roles Kategori):** CRUD role + permission (Spatie Permission), menu di bawah User (group Settings), hanya Super Admin | P0 | Super Admin dapat membuat/mengedit/menghapus role & mengatur permission per role |

### Status Transaksi

| Nilai | Label Indonesia | Keterangan |
|---|---|---|
| `pending` | Menunggu Persetujuan | Transaksi baru dibuat, menunggu approval |
| `approved` | Disetujui | Transaksi disetujui manajer/super admin |
| `cancelled` | Dibatalkan | Transaksi dibatalkan |

> Form transaksi: field **Status** & **Jenis Transaksi** berupa dropdown (tanpa opsi "Ditolak"); **Nomor Referensi** dibuat otomatis oleh sistem dengan prefix `INB` (Barang Masuk), `OUT` (Barang Keluar), `ADJ` (Penyesuaian Stok); **Approved By** hanya menampilkan akun role `manager` & `super_admin` dan otomatis terisi akun yang login; **Supplier** hanya menampilkan supplier aktif. Role **Staff** tidak melihat field Status/Approval dan hanya melihat transaksi miliknya sendiri.

### C. Notifikasi & Alerts

| ID | Kebutuhan | Prioritas | Kriteria Penerimaan |
|---|---|---|---|
| FR-09 | **Low-Stock Alert:** notifikasi saat stok ≤ Minimum Reorder Point | P0 | Muncul di icon lonceng dashboard **semua peran** (Super Admin, Manajer, Staf); mencakup daftar produk kritis |
| FR-10 | **Expiry Alert:** peringatan barang kedaluwarsa dalam 30/60/90 hari | P1 | Barang mendekati kedaluwarsa terdaftar di dashboard |

### D. Laporan & Ekspor

| ID | Kebutuhan | Prioritas | Kriteria Penerimaan |
|---|---|---|---|
| FR-11 | **Laporan Stok Real-Time** & riwayat transaksi | P0 | Data stok terkini dan mutasi dapat dilihat & dicetak |
| FR-12 | **Ekspor PDF & Excel** (background job untuk data besar) | P1 | Ekspor berjalan asinkronus; file dapat diunduh setelah selesai |
| FR-13 | **Grafik Dashboard:** tren inbound/outbound (7 hari) dan tren inbound 3 tahun | P1 | Grafik akurat terhadap data transaksi; hanya untuk Manajer/Super Admin |

> **Update (v2.1):** Grafik disamakan untuk **semua peran** — Line Chart tren transaksi **12 bulan terakhir** (Barang Masuk vs Barang Keluar) & Doughnut distribusi stok kategori, auto-refresh 30 detik, non-lazy (langsung tampil saat halaman dimuat).

### E. Keamanan & Audit

| ID | Kebutuhan | Prioritas | Kriteria Penerimaan |
|---|---|---|---|
| FR-14 | **Otentikasi & Otorisasi:** login aman, RBAC (Spatie Permission), token Sanctum untuk API | P0 | Pengguna hanya dapat mengakses modul sesuai perannya |
| FR-15 | **Audit Log:** pencatatan seluruh aktivitas penting (login, perubahan stok, modifikasi master data) | P0 | Setiap aksi penting tercatat: siapa, kapan, apa |
| FR-16 | **Proteksi brute-force** pada endpoint login | P0 | Login gagal berulang dibatasi / diblokir sementara |

---

## 3. Alur Sistem (System Flows)

### 3.1 Alur Inbound (Web & Mobile)

```
[Staf] Scan Barcode ──▶ Validasi Produk ──▶ Input Jumlah
      │                                        │
      │                                        ▼
      │                              Pilih Supplier + Lot + Expiry
      │                                        │
      ◀────────────────────────────────────────┘
                    Submit
                    ▼
        Stok bertambah + Log transaksi tercatat
```

### 3.2 Alur Outbound (Web & Mobile)

```
[Staf] Scan Barcode ──▶ Validasi Stok (FEFO/FIFO)
      │                          │
      │                          ├─ Cukup ──▶ Kurangi Stok + Log
      │                          │
      └── Tidak Cukup ──▶ Tampilkan Error / Blokir Transaksi
```

### 3.3 Alur Stock Opname

```
[Staf] Input Hasil Hitung Fisik ──▶ Hitung Selisih
      ──▶ [Manajer] Approve/Reject ──▶ Approved: Stok Disesuaikan + Log
```

---

## 4. API Endpoints (Mobile ↔ Backend)

| Method | Endpoint | Deskripsi | Peran |
|---|---|---|---|
| POST | `/api/login` | Autentikasi, mengembalikan Sanctum token | Semua |
| GET | `/api/dashboard` | Data dashboard per peran | Semua |
| GET | `/api/dashboard/chart` | Data grafik Inbound vs Outbound (7 hari) | Manajer/Super Admin |
| GET | `/api/products` | Daftar produk | Semua |
| GET | `/api/products/{barcode}` | Cari produk via barcode | Staf |
| POST | `/api/transactions` | Buat transaksi inbound/outbound | Staf |

> Semua endpoint terproteksi memerlukan header `Authorization: Bearer {token}` dan `Accept: application/json`.

---

## 5. Kebutuhan Non-Fungsional (Detail)

| Aspek | Kebutuhan |
|---|---|
| **Keamanan** | Enkripsi password, sanitasi input (anti SQL Injection/XSS), proteksi CSRF pada web, token Sanctum pada API |
| **Performa** | Paging & indeks pada query besar; ekspor asinkronus; target respons API < 500 ms untuk data normal |
| **Skalabilitas** | Mendukung > 1 juta SKU; struktur database siap diindeks |
| **Ketersediaan** | Kontainer Docker dengan `restart: unless-stopped`; backup DB terjadwal |
| **Audit** | Activity Log (spatie/laravel-activitylog) untuk jejak lengkap |

---

## 6. Matriks Ketertelusuran (Traceability)

| FR ID | BRD Section | PRD Section |
|---|---|---|
| FR-01 s.d. FR-04 | §4.1 (Master Data) | §3 (Fitur Web) |
| FR-05 s.d. FR-08 | §4.1 (Transaksi) | §5 (Alur Transaksi Inti) |
| FR-09 s.d. FR-10 | §4.1 (Notifikasi) | §1 (Fitur unggulan) |
| FR-11 s.d. FR-13 | §4.1 (Laporan) | §3 (Fitur Web) |
| FR-14 s.d. FR-16 | §5 (NFR) | §6 (Non-Fungsional) |
