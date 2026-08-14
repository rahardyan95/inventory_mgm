# Panduan Membuat GitHub Release (untuk Portofolio)

Release di GitHub menampilkan versi, catatan perubahan (changelog), dan **asset** (screenshot, build APK) — cara terbaik agar proyek terlihat profesional di portofolio.

## Opsi A: Melalui Website GitHub (tanpa install apa pun) — DIREKOMENDASIKAN

1. Buka repo: `https://github.com/rahardyan95/inventory_mgm`
2. Klik tab **Releases** di sisi kanan atas (di bawah nama repo), atau buka langsung `https://github.com/rahardyan95/inventory_mgm/releases`
3. Klik tombol **Draft a new release** (hijau)
4. Isi form:
   - **Choose a tag** → ketik `v2.4.0` lalu klik "Create new tag: v2.4.0 on publish" (tag akan dibuat otomatis dari branch `main`)
   - **Target** → `main`
   - **Release title** → `v2.4.0 - RBAC, Notifikasi Real-time, Anti-Fraud`
   - **Describe this release** → tempel template body di bawah
5. **Attach binaries/asset** (opsional tapi sangat disarankan untuk portofolio):
   - Klik area **"Attach binaries by dropping them here or selecting them"**
   - Pilih file screenshot dashboard (contoh: `docs/screenshots/dashboard.png`) atau file APK hasil build mobile
6. Centang **"Set as the latest release"**
7. Klik **Publish release**

> Screenshot: cara mengambil gambar dashboard — buka `http://localhost:8000/admin`, login dengan akun `admin@inventory.test` / `password`, lalu tekan `Win+Shift+S` (Snipping Tool) dan simpan ke `docs/screenshots/`.

## Opsi B: Menggunakan GitHub CLI (`gh`)

Install dulu (Windows):
```powershell
winget install --id GitHub.cli --accept-source-agreements --accept-package-agreements
```

Jika perintah `gh` tidak dikenali di terminal yang sedang terbuka (PATH belum dimuat):
```cmd
:: Opsi 1: buka terminal BARU (paling mudah — PATH langsung aktif)
:: Opsi 2: muat PATH di sesi berjalan tanpa menutup terminal
set PATH=%PATH%;C:\Program Files\GitHub CLI
```

Autentikasi (satu kali saja):
```powershell
gh auth login
# Pilih: GitHub.com > HTTPS > Authenticate Git with your GitHub credentials > Yes > Login with a web browser
# Browser terbuka > klik Authorize
```

Verifikasi login:
```powershell
gh auth status
# Contoh output sukses:
#   ✓ Logged in to github.com account <username> (keyring)
#   - Git operations protocol: https
```

Lalu buat release:
```powershell
# Buat tag + release (tanpa asset)
gh release create v2.4.0 --target main --title "v2.4.0 - RBAC, Notifikasi Real-time, Anti-Fraud" --notes-file docs/RELEASE_NOTES_v2.4.0.md

# Dengan asset screenshot/APK
gh release create v2.4.0 "docs/screenshots/dashboard.png" --target main --title "v2.4.0 - RBAC, Notifikasi Real-time, Anti-Fraud" --notes-file docs/RELEASE_NOTES_v2.4.0.md

# Update asset ke release yang sudah ada
gh release upload v2.4.0 docs/screenshots/dashboard.png
```

> **Catatan:** Tag `v2.4.0` dibuat otomatis saat release pertama. Untuk rilis berikutnya cukup ganti nomor versi (mis. `v2.5.0`) — tag baru akan dibuat otomatis juga.

**Release yang sudah dibuat:** https://github.com/rahardyan95/inventory_mgm/releases/tag/v2.4.0

---

## Template Body Release (untuk deskripsi)

```markdown
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
```

## Tips untuk Portofolio

- **Screenshot wajib**: GitHub akan menampilkan asset di halaman release — jauh lebih meyakinkan daripada teks saja.
- **Beri nama file screenshot deskriptif**: `dashboard.png`, `transaksi.png`, `mobile-scan.png`.
- **Gunakan versi konsisten**: versi release (`v2.4.0`) sebaiknya sinkron dengan `docs/CHECKPOINT.md`.
- **Buat satu release per rilis fitur besar** — riwayat release menunjukkan progres pengembangan.