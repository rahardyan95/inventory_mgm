# 📌 Checkpoint Pengembangan — Enterprise Inventory Management System

| Field | Nilai |
|---|---|
| **Versi Checkpoint** | v2.4.0 |
| **Tanggal** | 2026-08-14 |
| **Status** | ✅ Stabil — siap dikembangkan lebih lanjut |
| **Backend** | Laravel 13.8 + Filament 5.6 + PHP 8.3/8.4 |
| **Database** | PostgreSQL 16 (Docker) |
| **Mobile** | React Native (Expo) — terintegrasi via Sanctum API |
| **Test Suite** | 61 test PASS / 215 assertions |
| **Repository** | https://github.com/rahardyan95/inventory_mgm |

---

## 1. Ringkasan Status

Sistem Manajemen Inventaris Enterprise berjalan penuh di atas **Docker** (3 kontainer: `inventory_web`, `inventory_app`, `inventory_db`) dengan dashboard Filament berbahasa Indonesia, RBAC 3 peran, notifikasi real-time, dan auto-generate SKU/barcode. Repo kini memiliki **README.md profesional di root** (siap portofolio) dan **skrip backup database** `backup/backup-db.ps1` (hasil dump di `backup/dumps/`, tidak masuk git).

Dokumen pendukung: [BRD](./BRD.md) · [PRD](./PRD.md) · [FRD](./FRD.md) · [AKUN_DEMO](./AKUN_DEMO.md) · [SETUP](./SETUP.md) · [DOCKER](./DOCKER.md) · [README](./README.md)

---

## 2. Fitur yang Sudah Selesai

### 2.1 Dashboard (semua role)
- Judul **"Dashboard"** (bukan "Dasbor") di semua role
- Sapaan dinamis **waktu Indonesia WIB** (Pagi/Siang/Sore/Malam) dari `Asia/Jakarta`
- Stats cards gradient (biru/oranye/hijau/cyan) dengan sparkline **data nyata 7 hari**
- **Chart sama untuk semua role** (referensi AdminLTE):
  - `MonthlyTransactionsChart` — line chart Barang Masuk vs Barang Keluar, **12 bulan terakhir per bulan**, auto-refresh 30 detik
  - `StockDistributionChart` — doughnut distribusi stok per kategori, auto-refresh 30 detik
- Semua widget **non-lazy** (`$isLazy = false`) → langsung render setelah login
- Tabel "Transaksi Terkini" (staff melihat transaksi sendiri)

### 2.2 Notifikasi (semua role)
- Icon lonceng di topbar (samping profile) dengan **badge unread + animasi pulse**, polling 15 detik
- `InventoryNotificationService` — deduplikasi otomatis (tidak kirim ulang notifikasi yang sama):
  - **Low stock** → "Stok Menipis: {produk}" ke semua user
  - **Supplier non-aktif** → "Supplier Non-Aktif: {nama}" ke semua user
- Notifikasi persist (tidak hilang saat diklik, hanya tombol "Bersihkan")
- Terpicu: saat dashboard dimuat, setelah transaksi dibuat, setelah create/edit supplier

### 2.3 Role-Based Access Control (RBAC)

| Fitur | Super Admin | Manajer | Staf |
|---|---|---|---|
| Dashboard + chart | ✅ | ✅ | ✅ |
| Produk — lihat/buat/hapus | ✅ | ✅ | ✅ |
| Produk — **edit** | ✅ | ✅ | ❌ |
| Kategori — CRUD | ✅ | ✅ | ❌ |
| Supplier — CRUD | ✅ | ✅ | ❌ (hanya lihat) |
| Transaksi — buat | ✅ | ✅ | ✅ |
| Transaksi — edit miliknya (pending) | ✅ | ✅ | ✅ (hanya miliknya & belum approved) |
| Transaksi — hapus | ✅ | ✅ | ❌ |
| Manajemen User | ✅ | ❌ | ❌ |
| **Roles Kategori** (role + permission) | ✅ | ❌ | ❌ |
| Audit Log | ✅ | ❌ | ❌ |

### 2.4 Produk
- SKU & Barcode **auto-generate** (read-only, required):
  - SKU mengikuti kategori: `{PREFIX}-###` (mis. `ELE-001`)
  - Barcode 13 digit unik (EAN-13 style), siap scan mobile
- **SKU reuse**: produk yang dihapus (soft delete) membebaskan nomornya — produk baru memakai nomor terkecil tersedia (tidak melompat ke SKU selanjutnya). Unique constraint SKU/barcode menjadi **partial unique index** (`WHERE deleted_at IS NULL`)
- Unit dropdown (pcs, box, pack, kg, liter, dll)
- Date picker seragam `DD/MM/YYYY`
- Image upload (integrasi kamera mobile menyusul)

### 2.5 Supplier
- Form standar warehouse: Nama Perusahaan, Kontak Person, Email, Telepon, **NPWP**, **Ketentuan Pembayaran** (COD/Net 7/14/30/60), Alamat, Catatan, Status Aktif
- Staff: hanya melihat (403 untuk create/edit/delete)
- Supplier non-aktif tidak muncul di pilihan transaksi + memicu notifikasi

### 2.6 Transaksi
- Nomor referensi otomatis: `INB-YYYYMMDD-###` (Barang Masuk), `OUT-...` (Barang Keluar), `ADJ-...` (Penyesuaian Stok)
- **Anti-fraud**:
  - "Dibuat Oleh" & "Disetujui Oleh" otomatis mengikuti akun login (disabled)
  - "Disetujui Oleh" format **"Nama - Role Indonesia"** (mis. "Budi Manajer - Manajer")
  - **Field immutable saat edit** (dikunci UI + server): Jenis Transaksi, Nomor Referensi, Tanggal Transaksi, Tanggal Persetujuan
  - Staff tidak bisa melihat/mengisi status & approval
- Status dropdown: Menunggu Persetujuan / Disetujui / Dibatalkan
- Tabel: badge berwarna, filter jenis & status, kolom sekunder toggleable (tanpa scroll samping)
- **Staff**: bisa edit transaksi miliknya yang `pending`; transaksi `approved` tidak bisa diedit; tidak bisa hapus

### 2.7 Login
- Halaman login dengan **3 tombol akun demo** (auto-fill email & password): Super Admin, Manajer, Staf
- Proteksi brute-force bawaan Filament

---

## 3. Perbaikan Penting (Bugs yang Sudah Di-Fix)

| # | Masalah | Solusi |
|---|---|---|
| 1 | Chart tidak muncul setelah login | `APP_URL=http://localhost:8000` — asset chart.js di-load via `asset()` tanpa port → 404 |
| 2 | Asset Filament tidak ter-publish | `php artisan filament:assets` (chart.js kini di `public/js/filament/widgets/components/chart.js`) + `storage:link` |
| 3 | "Dasbor" tidak berubah jadi "Dashboard" | `static $title`/`$navigationLabel` di `Dashboard.php` + lang override di `lang/vendor/filament-panels/id/pages/dashboard.php` |
| 4 | Greeting tidak sesuai waktu Indonesia | `APP_TIMEZONE=Asia/Jakarta` + hitung sapaan dari `now('Asia/Jakarta')` |
| 5 | BadMethodCallException `mount()` di Dashboard | Filament 5 tidak punya `parent::mount()` — hapus panggilan parent |
| 6 | `readOnly()` tidak ada di Select Filament 5 | Gunakan `disabled() + dehydrated()` |
| 7 | `canEdit(Transaction $record)` signature error | Gunakan `$record` tanpa type (kompatibel `Model $record`) |
| 8 | Tabel `supplier.name` error (model tidak punya kolom `name`) | Ganti ke `supplier.company_name` |
| 9 | SKU melompat setelah produk dihapus | Partial unique index + perhitungan nomor terkecil tersedia |
| 10 | Lonceng notifikasi tidak muncul | Extends `Filament\Livewire\DatabaseNotifications` (bukan base) + `isLazy: false` |

---

## 4. Struktur Kode (Area Utama)

```
backend/app/
├── Filament/
│   ├── Auth/Login.php                      ← Login + akun demo auto-fill
│   ├── Components/PersistentDatabaseNotifications.php
│   ├── Pages/Dashboard.php                 ← Title "Dashboard", sapaan WIB, sinkron notifikasi
│   ├── Resources/
│   │   ├── Categories/                     ← CRUD (admin/manager)
│   │   ├── Products/                       ← SKU/barcode auto, reuse SKU, RBAC staff
│   │   ├── Roles/                          ← Roles Kategori (CRUD + permission, super admin)
│   │   ├── Suppliers/                      ← Form warehouse, staff view-only
│   │   ├── Transactions/                   ← Anti-fraud, field immutable, RBAC staff
│   │   └── Users/                          ← Manajemen user (super admin)
│   └── Widgets/
│       ├── InventoryStatsWidget.php        ← Stats gradient + sparkline data nyata
│       ├── MonthlyTransactionsChart.php    ← Line chart 12 bulan (semua role)
│       ├── StockDistributionChart.php      ← Doughnut kategori (semua role)
│       └── RecentTransactionsWidget.php    ← Tabel transaksi terkini
├── Models/                                 ← Product, Supplier, Transaction, Category, User
├── Services/
│   ├── InventoryService.php                ← Logika stok (inbound/outbound/adjustment)
│   └── InventoryNotificationService.php    ← Notifikasi low stock + supplier non-aktif
└── Providers/Filament/AdminPanelProvider.php ← Panel, DB notifications, CSS kustom
```

---

## 5. Cara Menjalankan (Checkpoint Ini)

```powershell
# 1. Pastikan Docker Desktop aktif, lalu di root proyek:
docker-compose up -d --build

# 2. Setup aplikasi (hanya pertama kali / setelah clone):
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
docker-compose exec app php artisan storage:link

# 3. Publish aset Filament (wajib untuk chart & UI):
docker-compose exec app php artisan filament:assets

# 4. Build aset Vite (CSS kustom dashboard):
#    (di host, folder backend)
npm run build

# 5. Akses:
#    http://localhost:8000/admin
#    Akun demo: admin@inventory.test / manager@inventory.test / staff@inventory.test (password: password)
```

> **PENTING:** `.env` harus berisi `APP_URL=http://localhost:8000` dan `APP_TIMEZONE=Asia/Jakarta` agar chart muncul & waktu sesuai WIB.

---

## 6. Testing

```powershell
docker-compose exec app php artisan test
```

**Hasil:** `61 passed (215 assertions)` — mencakup:
- CRUD semua resource (Product, Supplier, Category, Transaction, User)
- RBAC (staff 403 di area terlarang, visibilitas sendiri)
- Anti-fraud (field immutable, approved tidak bisa diedit staff)
- Notifikasi (lonceng render, badge unread, dedupe)
- Chart (render di dashboard semua role, URL aset valid)
- SKU reuse (reuse setelah delete, isi gap, lanjut bila penuh)
- Dashboard (title "Dashboard", tanpa teks "peran:")
- API mobile (auth Sanctum, transaksi)

---

## 7. Roadmap Pengembangan Selanjutnya (Ide)

### Prioritas Tinggi
1. **Integrasi kamera mobile** — upload gambar produk dari aplikasi mobile (helper text sudah disiapkan di form)
2. **Modul Stock Opname** — pencatatan selisih fisik + alur approval Manajer (FR-07), skema transaksi `adjustment` sudah ada
3. **Ekspor PDF/Excel asinkronus** (FR-12) — background job untuk data besar
4. **Expiry Alert** (FR-10) — notifikasi barang kedaluwarsa 30/60/90 hari (scope `nearExpiry` sudah ada di model)

### Prioritas Sedang
5. **Filter & pencarian lanjutan** di tabel transaksi (rentang tanggal, produk, staf)
6. **Multi-zona waktu** (WIB/WITA/WIT) — tambah field timezone per user jika diperlukan
7. **Grafik filter interaktif** — pilih rentang bulan di chart
8. **Soft-delete consistency** — tombol restore/force delete yang rapi di semua resource

### Prioritas Rendah / Fase 2
9. Integrasi POS pihak ketiga (Out-of-Scope Fase 1)
10. Modul akuntansi penuh
11. Deploy production ke VPS (panduan siap di [DEPLOY_VPS.md](./DEPLOY_VPS.md))
12. Build APK/IPA mobile ([BUILD_MOBILE.md](./BUILD_MOBILE.md))

---

## 8. Catatan Penting (Gotchas)

- **`filament:assets` wajib dijalankan** setelah `composer install` di mesin baru, jika tidak chart & beberapa UI tidak muncul
- **`APP_URL` harus memakai port `:8000`** di environment Docker lokal, jika tidak semua URL aset (chart.js, CSS) mengarah ke port 80 yang kosong
- **Timezone aplikasi adalah `Asia/Jakarta` (WIB)** — keputusan desain satu zona untuk seluruh pengguna
- Filament 5 **tidak punya `mount()` pada parent page** — hook lifecycle via trait (`booted*`)
- Field `Select` di Filament 5 tidak punya `readOnly()` — gunakan `disabled() + dehydrated()`
- Unique constraint SKU/barcode bersifat **partial** (`WHERE deleted_at IS NULL`) — jangan menambahkan unique constraint penuh kembali
