# Dashboard Role-Based Features & Documentation

Poin yang akan dikerjakan:
1. Mematikan simulator iOS (sudah dilakukan).
2. Membuat grafik pada dashboard untuk akun Manajer dan Super Admin.
3. Menyesuaikan dashboard Staf untuk menampilkan produk yang baru masuk (riwayat *inbound* terbaru).
4. Membuat file PRD (Product Requirements Document) berisi catatan akun demo.

## User Review Required
- Saya akan menginstal `react-native-chart-kit` untuk menampilkan grafik di aplikasi mobile. Apakah Anda setuju dengan pustaka ini (ini standar dan aman untuk Expo)?
- Untuk poin 3 (Dashboard Staf), saya akan mengubah daftar "Produk Terbaru" (yang saat ini menampilkan semua produk terbaru) menjadi "Riwayat Masuk (Inbound) Terbaru" agar staf bisa melihat barang apa saja yang baru berhasil di-scan/masuk.

## Proposed Changes

### Backend API
#### [MODIFY] `routes/api.php`
- Menambahkan endpoint `GET /dashboard/chart` untuk mengambil data transaksi (Inbound vs Outbound) selama 7 hari terakhir.
- Endpoint ini akan digunakan oleh grafik di dashboard Manajer & Super Admin.

### Mobile App (Frontend)
#### [NEW DEPENDENCY] `react-native-chart-kit`, `react-native-svg`
- Menginstal dependensi untuk grafik (Chart Kit memerlukan SVG).

#### [MODIFY] `src/screens/DashboardScreen.js`
- Menambahkan logika *role-based rendering*.
- **Jika user adalah `manager` atau `super_admin`:** Tampilkan grafik (mengambil data dari `/dashboard/chart`).
- **Jika user adalah `staff`:** Ubah bagian produk menjadi daftar transaksi masuk terbaru (inbound).

### Documentation
#### [NEW] `docs/PRD.md`
- Membuat dokumen PRD sederhana yang mencantumkan kredensial (email & password) untuk akun Manajer, Super Admin, dan Staf.

## Verification Plan
1. Menjalankan ulang aplikasi mobile di iOS Simulator.
2. Login sebagai **Super Admin / Manager** dan memverifikasi grafik muncul dengan benar.
3. Login sebagai **Staff** dan memverifikasi bahwa grafik tidak muncul, tetapi daftar produk inbound muncul.
4. Memastikan file `PRD.md` sudah dibuat dan dapat dibaca.
