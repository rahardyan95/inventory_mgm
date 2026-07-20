# 📱 Panduan Build APK Android & iOS (Expo EAS Build)

Dokumen ini menjelaskan cara membuat file instalasi aplikasi mobile untuk **Android (.apk / .aab)** dan **iOS (.ipa)** dari proyek React Native (Expo) ini.

---

## Prasyarat

| Tools | Versi | Keterangan |
|-------|-------|------------|
| Node.js | ≥ 18 | Sudah terinstall |
| Expo CLI | Latest | `npm install -g expo-cli` |
| EAS CLI | Latest | `npm install -g eas-cli` |
| Akun Expo | - | Buat gratis di expo.dev |
| Android Studio | Latest | Untuk build lokal Android |
| Xcode | ≥ 14 | **Mac only** — Untuk build iOS |
| Apple Developer Account | $99/tahun | Untuk distribusi iOS ke App Store |

---

## Opsi Build: Cloud (EAS) vs Lokal

| Metode | Kelebihan | Kekurangan |
|--------|-----------|------------|
| **EAS Build (Cloud)** | Tidak perlu Android Studio/Xcode. Gratis (terbatas). | Perlu upload kode ke Expo Cloud |
| **Build Lokal Android** | Tidak perlu internet. Gratis. | Perlu Android Studio |
| **Build Lokal iOS** | Gratis | **Wajib Mac + Xcode** |

---

## ⚠️ Sebelum Build: Update URL API

**PENTING!** Sebelum build untuk production/distribusi, pastikan URL API di file `mobile/src/services/api.js` sudah diubah ke URL VPS Anda:

```javascript
// DEVELOPMENT (emulator)
const API_URL = 'http://10.0.2.2:8000/api';

// PRODUCTION (ganti dengan URL VPS Anda!)
const API_URL = 'https://inventory.perusahaan.com/api';
```

---

## Metode 1: EAS Build (Cloud — Direkomendasikan)

Cara termudah — build dilakukan di server Expo, Anda hanya perlu upload kode.

### Langkah 1: Setup Akun & Login

```powershell
# Install EAS CLI
npm install -g eas-cli

# Login ke akun Expo Anda
eas login

# Verifikasi login
eas whoami
```

### Langkah 2: Inisialisasi EAS di Proyek

```powershell
cd C:\Users\Rahardyan\Desktop\Project\inventory_mgm\mobile

# Inisialisasi konfigurasi EAS
eas build:configure
```

Perintah ini akan membuat file `eas.json` di folder mobile. Anda bisa menyesuaikannya:

```json
{
  "cli": {
    "version": ">= 5.0.0"
  },
  "build": {
    "development": {
      "developmentClient": true,
      "distribution": "internal"
    },
    "preview": {
      "distribution": "internal",
      "android": {
        "buildType": "apk"
      }
    },
    "production": {
      "android": {
        "buildType": "app-bundle"
      }
    }
  },
  "submit": {
    "production": {}
  }
}
```

### Langkah 3: Build APK Android (untuk Testing/Distribusi Internal)

```powershell
# Build APK untuk distribusi internal (testing)
eas build --platform android --profile preview

# Build AAB untuk Google Play Store (production)
eas build --platform android --profile production
```

- Proses build berjalan di cloud (~10-20 menit)
- Link download APK akan diberikan di terminal dan email
- Download APK dan install di Android

### Langkah 4: Build IPA iOS (untuk Testing/Distribusi Internal)

```powershell
# Build IPA untuk TestFlight / distribusi internal
eas build --platform ios --profile preview
```

> ⚠️ **Catatan iOS:** Diperlukan **Apple Developer Account** ($99/tahun) untuk sign aplikasi. EAS akan memandu setup provisioning profile.

### Langkah 5: Build Kedua Platform Sekaligus

```powershell
eas build --platform all --profile preview
```

---

## Metode 2: Build APK Lokal (Android Studio)

Cara ini tidak memerlukan akun Expo Cloud.

### Langkah 1: Generate Project Native

```powershell
cd C:\Users\Rahardyan\Desktop\Project\inventory_mgm\mobile

# Buat folder android/ dan ios/ (prebuild)
npx expo prebuild

# Atau jika ingin clean build:
npx expo prebuild --clean
```

### Langkah 2: Build APK Debug (untuk Testing)

```powershell
# Build APK debug menggunakan Gradle
cd android
.\gradlew assembleDebug

# File APK tersimpan di:
# android/app/build/outputs/apk/debug/app-debug.apk
```

### Langkah 3: Build APK Release (untuk Distribusi)

**A. Buat Keystore (hanya sekali — SIMPAN BAIK-BAIK!)**
```powershell
# Buat keystore untuk signing APK
keytool -genkey -v -keystore inventory-release.keystore -alias inventory -keyalg RSA -keysize 2048 -validity 10000

# Ikuti prompts: masukkan password, nama, organisasi, dll.
# SIMPAN file .keystore dan passwordnya di tempat aman!
```

**B. Konfigurasi Signing di android/app/build.gradle**
```gradle
android {
    signingConfigs {
        release {
            storeFile file('../../inventory-release.keystore')
            storePassword 'PASSWORD_ANDA'
            keyAlias 'inventory'
            keyPassword 'PASSWORD_ANDA'
        }
    }
    buildTypes {
        release {
            signingConfig signingConfigs.release
            minifyEnabled true
            proguardFiles getDefaultProguardFile('proguard-android.txt'), 'proguard-rules.pro'
        }
    }
}
```

**C. Build APK Release**
```powershell
cd android
.\gradlew assembleRelease

# File APK Release tersimpan di:
# android/app/build/outputs/apk/release/app-release.apk
```

---

## Cara Instalasi di Android

### Instalasi APK (Sideload)

1. **Transfer APK ke perangkat** via USB atau kirim via WhatsApp/Email
2. Di Android, buka **Pengaturan → Keamanan → Izinkan sumber tidak dikenal**
   - Atau di Android 8+: Izinkan aplikasi (File Manager/Chrome) install APK
3. Buka file APK di perangkat → Tap **Instal**
4. Buka aplikasi **Enterprise Inventory**

### Distribusi via Google Play Store

1. Buat **Google Play Console** di play.google.com/console ($25 sekali bayar)
2. Upload file `.aab` (Android App Bundle) dari hasil build
3. Isi metadata: nama, deskripsi, screenshot
4. Submit untuk review (1-3 hari)
5. Setelah disetujui, unduh via Play Store

---

## Cara Instalasi di iOS

### Opsi A: TestFlight (Direkomendasikan untuk Testing)

1. Buat akun **Apple Developer** di developer.apple.com
2. Build dengan EAS: `eas build --platform ios --profile preview`
3. Upload ke App Store Connect: `eas submit --platform ios`
4. Di App Store Connect, buka **TestFlight** → tambah penguji (via email)
5. Penguji terima email undangan → install app **TestFlight** di iPhone → install aplikasi

### Opsi B: App Store (Production)

1. Pastikan APK/IPA sudah siap dan sudah ditest di TestFlight
2. Buat **App Store listing** di App Store Connect
3. Submit untuk review Apple (~1-7 hari)
4. Setelah approved, publish ke App Store

### Opsi C: Ad Hoc Distribution (tanpa App Store — Max 100 perangkat)

1. Kumpulkan **UDID** perangkat iOS yang akan dipakai
2. Daftarkan UDID di Apple Developer Console
3. Build dengan EAS: `eas build --platform ios --profile preview`
4. Kirim file `.ipa` dan install via AltStore atau Apple Configurator

---

## Konfigurasi `app.json` yang Penting

```json
{
  "expo": {
    "name": "Enterprise Inventory",
    "slug": "inventory-mgm",
    "version": "1.0.0",
    "orientation": "portrait",
    "icon": "./assets/icon.png",
    "splash": {
      "image": "./assets/splash.png",
      "backgroundColor": "#0f172a"
    },
    "android": {
      "package": "com.perusahaan.inventory",    // ID unik aplikasi Android
      "versionCode": 1,                           // Increment setiap update
      "permissions": ["CAMERA"]                   // Diperlukan untuk scanner
    },
    "ios": {
      "bundleIdentifier": "com.perusahaan.inventory",  // ID unik aplikasi iOS
      "buildNumber": "1",                               // Increment setiap update
      "infoPlist": {
        "NSCameraUsageDescription": "Diperlukan untuk scan barcode produk."
      }
    }
  }
}
```

---

## Checklist Sebelum Build Production

- [ ] `API_URL` di `api.js` sudah diubah ke URL VPS production
- [ ] `APP_ENV` di backend `.env` sudah `production`
- [ ] Versi app di `app.json` sudah diupdate
- [ ] `versionCode` (Android) / `buildNumber` (iOS) sudah di-increment
- [ ] Ikon aplikasi (`assets/icon.png`) sudah disiapkan (1024×1024px)
- [ ] Splash screen (`assets/splash.png`) sudah disiapkan
- [ ] File Keystore Android disimpan dengan aman (untuk update berikutnya!)
- [ ] Test di perangkat fisik sebelum submit ke store
