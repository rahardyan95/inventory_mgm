# Integrasi Mobile-Web, Setup CRUD & Modifikasi Role Sistem

Rencana ini dibuat untuk merespons permintaan Anda terkait pembangunan aplikasi mobile terintegrasi, fungsionalitas penuh CRUD, serta penyesuaian hak akses (Role) di dalam sistem. 

## Tujuan Utama
1. **Aplikasi Mobile (React Native/Expo):** Membangun UI lengkap (Login, Dashboard, Scanner, Form Transaksi) dengan tema warna *soft dark-slate/blue* yang persis sama dengan Web.
2. **Fungsionalitas CRUD:** Memastikan Filament Web dan API Mobile mampu melakukan fungsi *Create, Read, Update, Delete* dengan sempurna terhadap produk dan transaksi.
3. **Manajemen Akun (User Resource):** Membangun fitur di Web Dashboard agar Super Admin bisa membuat akun Staf Gudang dengan mudah.
4. **Hierarki Role Baru:** Menjadikan "Manager" sebagai pemegang hak akses tertinggi di aplikasi, sementara "Super Admin" difokuskan untuk tugas pembuatan akun/IT.

> [!IMPORTANT]
> **User Review Required**
> Terdapat perubahan logika hierarki akses (Role) yang perlu Anda setujui sebelum saya mengeksekusinya. Silakan baca bagian **Open Questions** di bawah.

## Open Questions

1. Anda menyebutkan *"akun manajer role paling tinggi"* namun juga *"super admin bisa membuat akun staf"*. 
   Apakah Anda setuju jika hak aksesnya dibagi seperti ini:
   - **Manager:** Memiliki **SEMUA** akses ke operasional bisnis (Produk, Supplier, Transaksi, Laporan, Approve), tidak bisa menghapus/membuat akun.
   - **Super Admin:** Hanya untuk keperluan IT (Membuat/Menghapus Akun Staf/Manajer) dan perbaikan sistem.
   Jika Anda setuju dengan skema ini, klik **Proceed/Lanjutkan** dan saya akan mengeksekusinya.

## Proposed Changes

---

### Backend & Web Dashboard (Laravel & Filament)

#### [MODIFY] `database/seeders/RoleSeeder.php`
- Menata ulang hak akses (permissions) agar `manager` mendapatkan prioritas tertinggi dalam operasi bisnis.

#### [NEW] `app/Filament/Resources/UserResource.php`
- Membuat antarmuka CRUD (Create, Read, Update, Delete) khusus untuk manajemen pengguna.
- Membatasi visibilitas menu ini **hanya untuk Super Admin** menggunakan Spatie Permission. Di menu ini, Super Admin bisa mengisi email, password, dan memilih role "Staff".

---

### Mobile Application (React Native / Expo)

Akan dibuat struktur lengkap aplikasi Android/iOS dengan skema warna `slate-900` (#0f172a) dan `blue-500` (#0ea5e9) persis seperti halaman depan Web.

#### [NEW] `mobile/src/screens/LoginScreen.js`
- Halaman otentikasi. Staf gudang memasukkan email dan password untuk mendapatkan *Sanctum Token*.
- Desain *Dark Mode*, *Glassmorphism* ringan, selaras dengan web.

#### [NEW] `mobile/src/screens/DashboardScreen.js`
- Halaman beranda setelah staf login. Menampilkan ringkasan singkat dan tombol besar menuju fitur "Scan Barcode".

#### [MODIFY] `mobile/src/screens/ScannerScreen.js`
- Menyempurnakan alur *Scanner*. Setelah barcode terbaca, staf tidak hanya melihat data, tetapi langsung diarahkan ke form transaksi.

#### [NEW] `mobile/src/screens/TransactionFormScreen.js`
- Layar baru tempat staf mengisi jumlah barang (Inbound/Outbound) dari hasil pindai barcode dan men-submit data ke API Laravel secara aman (CRUD berfungsi).

## Verification Plan

### Automated & Manual Testing
- **API Tests:** Menjalankan unit test untuk memastikan staf yang tidak punya role "Manager" tidak bisa menghapus data sembarangan, namun bisa memasukkan data transaksi lewat mobile.
- **Mobile Build:** Melakukan *test-run* aplikasi Expo untuk memverifikasi navigasi dari Login -> Dashboard -> Scan -> Form Transaksi berjalan mulus tanpa celah *error* API.
