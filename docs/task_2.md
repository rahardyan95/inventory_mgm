# Task List: Implementasi Mobile & Role System

## Backend & Web Dashboard (Laravel + Filament)
- [x] Modifikasi `RoleSeeder.php` (Hierarki Manager & Super Admin)
- [x] Re-run Seeder untuk memperbarui Permission
- [x] Buat `UserResource.php` di Filament (Pembuatan akun Staf)
- [x] Batasi hak akses `UserResource` hanya untuk Super Admin

## Aplikasi Mobile (React Native / Expo)
- [x] Buat `LoginScreen.js` (Sanctum Auth & Dark Theme)
- [x] Buat `DashboardScreen.js` (Ringkasan & Tombol Scan)
- [x] Update `ScannerScreen.js` (Auto-navigate ke Form Transaksi)
- [x] Buat `TransactionFormScreen.js` (Input jumlah Inbound/Outbound)
- [x] Setup Navigasi Lengkap di `App.js`

## Verifikasi & QA
- [x] Coba Login via Mobile API (Sukses)
- [x] Coba Create Transaksi via Mobile API (Sukses, Stok Bertambah)
