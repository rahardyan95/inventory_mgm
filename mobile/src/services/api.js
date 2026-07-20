/**
 * ==========================================================
 * Service: API Client (Axios)
 * ==========================================================
 *
 * Konfigurasi terpusat untuk semua komunikasi HTTP antara
 * aplikasi mobile React Native dan backend Laravel.
 *
 * Menggunakan library Axios dengan konfigurasi:
 * - Base URL: URL backend berdasarkan environment
 * - Headers: JSON content type untuk semua request
 * - Interceptor: Token Bearer Sanctum disisipkan otomatis
 *
 * ============================================================
 * PANDUAN KONFIGURASI URL API
 * ============================================================
 *
 * Ubah nilai `API_URL` sesuai environment yang digunakan:
 *
 * 1. EMULATOR ANDROID (pengembangan lokal):
 *    const API_URL = 'http://10.0.2.2:8000/api';
 *    → `10.0.2.2` adalah alamat khusus yang merujuk ke localhost komputer host
 *      dari dalam Android Emulator (127.0.0.1 tidak akan berfungsi!)
 *
 * 2. HP/PERANGKAT FISIK (testing di jaringan WiFi yang sama):
 *    const API_URL = 'http://192.168.1.100:8000/api';
 *    → Ganti dengan IP LAN komputer Anda (cek via `ipconfig` di Windows)
 *    → Pastikan komputer dan HP terhubung ke WiFi yang sama
 *
 * 3. iOS SIMULATOR (hanya Mac):
 *    const API_URL = 'http://localhost:8000/api';
 *    → iOS Simulator bisa menggunakan localhost langsung
 *
 * 4. PRODUCTION / VPS:
 *    const API_URL = 'https://inventory.perusahaan.com/api';
 *    → Ganti dengan URL domain VPS Anda sebelum build APK/IPA
 *    → Pastikan menggunakan HTTPS!
 */

import axios from 'axios';

// ============================================================
// Konfigurasi URL — SESUAIKAN dengan environment Anda!
// ============================================================

// Untuk emulator Android (mode development):
const API_URL = 'http://10.0.2.2:8000/api';

// Uncomment baris yang sesuai dan comment baris di atas:
// const API_URL = 'http://192.168.1.100:8000/api'; // HP fisik (sesuaikan IP)
// const API_URL = 'http://localhost:8000/api';       // iOS Simulator
// const API_URL = 'https://inventory.perusahaan.com/api'; // Production VPS

/**
 * Instance Axios dengan konfigurasi bawaan.
 * Semua request API akan menggunakan instance ini.
 */
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',  // Semua request dalam format JSON
    'Accept':       'application/json',  // Minta respons dalam format JSON
  },
  timeout: 10000, // Timeout 10 detik (cegah request menggantung terlalu lama)
});

/**
 * Mengatur atau menghapus Bearer Token di header Authorization.
 *
 * Fungsi ini dipanggil dari LoginScreen setelah berhasil login,
 * dan dari saat logout untuk menghapus token.
 *
 * Token Sanctum dari Laravel disimpan di memori (bukan persistent storage).
 * Untuk implementasi penuh: simpan token di SecureStore (expo-secure-store)
 * agar token tetap ada setelah aplikasi ditutup/dibuka kembali.
 *
 * @param {string|null} token - Token Sanctum, atau null untuk menghapus
 *
 * @example
 * // Set token setelah login
 * setAuthToken('1|abc123xyz...');
 *
 * // Hapus token saat logout
 * setAuthToken(null);
 */
export const setAuthToken = (token) => {
  if (token) {
    // Sisipkan token ke header Authorization untuk semua request berikutnya
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    // Hapus header Authorization (saat logout)
    delete api.defaults.headers.common['Authorization'];
  }
};

// Export instance API sebagai default untuk digunakan di semua screen/service
export default api;
