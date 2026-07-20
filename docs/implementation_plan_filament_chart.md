# Implementation Plan: Grafik Inbound 3 Tahun Terakhir

## Tujuan
Menambahkan grafik barang masuk (inbound) per bulan untuk 3 tahun terakhir ke Dasbor Web (Filament Admin), khusus untuk Super Admin dan Manajer.

## User Review Required
> [!IMPORTANT]
> Saya akan membuat grafik garis (Line Chart) di mana sumbu X adalah Bulan (Januari-Desember) dan terdapat 3 garis/dataset terpisah yang mewakili masing-masing tahun (Tahun Ini, Tahun Lalu, dan 2 Tahun Lalu). Apakah format visual ini sesuai dengan yang Anda maksud dari "kategorikan dalam 3 tahun terakhir"?

## Proposed Changes

### Backend (Filament)
#### [NEW] `app/Filament/Widgets/InboundTransactionsChart.php`
- Membuat *widget* grafik kustom menggunakan Filament Chart Widget.
- **Query Data:** Mengambil jumlah total barang masuk (`sum(quantity)`) dari tabel `transaction_items` yang terkait dengan `transactions` berstatus `inbound`.
- Data akan dikelompokkan berdasarkan bulan (1-12) dan dibagi menjadi 3 dataset (Tahun ini, Tahun - 1, Tahun - 2).
- **Access Control:** Menambahkan metode `public static function canView(): bool` untuk memastikan hanya *role* `super_admin` dan `manager` yang bisa melihat *widget* ini.

#### [MODIFY] `app/Providers/Filament/AdminPanelProvider.php`
- Mendaftarkan *widget* baru ke dalam *dashboard* (apabila belum terdeteksi secara otomatis melalui `discoverWidgets`).

## Verification Plan
1. Membuat/menjalankan kode *widget* grafik.
2. Memverifikasi bahwa data bisa dirender tanpa *error*.
3. Memastikan Staf tidak dapat melihat *widget* tersebut saat mencoba mengakses Dasbor Filament.
