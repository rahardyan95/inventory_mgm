## Fitur Baru
- **RBAC Roles Kategori** — CRUD role + permission, khusus Super Admin
- **Notifikasi Real-time** — lonceng dengan badge unread, alert low stock & supplier non-aktif, deduplikasi otomatis
- **Anti-Fraud Transaksi** — field immutable (jenis, nomor referensi, tanggal), approval otomatis mengikuti akun login
- **SKU & Barcode Auto-Generate** — EAN-13, dengan SKU reuse setelah soft-delete
- **Login 3 Akun Demo** — tombol auto-fill (Super Admin, Manajer, Staf)
- **Dashboard** — chart Barang Masuk/Keluar 12 bulan & doughnut stok per kategori (semua role), sapaan WIB

## Perbaikan
- Fix chart tidak muncul setelah login (APP_URL + filament:assets)
- Fix "Dasbor" → "Dashboard" di semua role
- Fix SKU melompat setelah produk dihapus (partial unique index)
- Fix lonceng notifikasi tidak muncul (extend DatabaseNotifications + isLazy:false)

## Teknologi
- Backend: Laravel 13.8, Filament 5.6, PHP 8.3/8.4, PostgreSQL 16, Spatie Permission, Sanctum
- Mobile: React Native (Expo SDK 57)
- Infra: Docker Compose

## Pengujian
- 61 test PASS / 215 assertions (CRUD, RBAC, anti-fraud, notifikasi, chart, SKU reuse, API mobile)

## Cara Menjalankan
1. `docker-compose up -d --build`
2. `docker-compose exec app composer install && php artisan key:generate && php artisan migrate --force && php artisan db:seed --force`
3. `docker-compose exec app php artisan storage:link && php artisan filament:assets`
4. `npm run build` (dari folder backend)
5. Buka `http://localhost:8000/admin`