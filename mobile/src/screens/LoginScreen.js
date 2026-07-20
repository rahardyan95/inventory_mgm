/**
 * ==========================================================
 * Screen: LoginScreen
 * ==========================================================
 *
 * Halaman masuk (login) untuk aplikasi mobile Staf Gudang.
 * Berkomunikasi dengan endpoint POST /api/login di backend Laravel.
 *
 * Alur Login:
 * 1. User mengisi email dan password
 * 2. App mengirim request ke API /login dengan `device_name`
 * 3. Jika berhasil, token Sanctum disimpan di header Axios
 * 4. User diarahkan ke Dashboard dengan data profil sebagai parameter
 *
 * Akun Default untuk Testing:
 * - Super Admin : admin@inventory.test / password
 * - Manager     : manager@inventory.test / password
 * - Staff       : staff@inventory.test / password
 */

import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, Alert, ActivityIndicator } from 'react-native';
import api, { setAuthToken } from '../services/api';

export default function LoginScreen({ navigation }) {
  // State untuk field form login
  const [email, setEmail]       = useState('staff@inventory.test'); // Default untuk kemudahan testing
  const [password, setPassword] = useState('password');
  const [loading, setLoading]   = useState(false); // Menampilkan spinner saat proses login

  /**
   * Menangani proses login saat tombol "Masuk" ditekan.
   * Mengirim kredensial ke API dan menyimpan token autentikasi.
   */
  const handleLogin = async () => {
    // Validasi form dasar sebelum request ke server
    if (!email || !password) {
      Alert.alert('Error', 'Harap isi email dan password.');
      return;
    }

    setLoading(true);
    try {
      // Kirim request ke endpoint login Laravel Sanctum
      // `device_name` wajib diisi — digunakan untuk memberi nama token di database
      const response = await api.post('/login', {
        email:       email,
        password:    password,
        device_name: 'MobileApp', // Identifier token di tabel personal_access_tokens
      });

      if (response.data.token) {
        // Simpan token ke header Authorization Axios (Bearer Token)
        // Berlaku untuk semua request selanjutnya selama sesi ini
        setAuthToken(response.data.token);
        
        // Redirect ke Dashboard, kirim data user sebagai route parameter
        // `navigation.replace` digunakan agar halaman login tidak bisa diakses dengan tombol Back
        navigation.replace('Dashboard', { user: response.data.user });
      }
    } catch (error) {
      // Error 422: Kredensial salah (email/password tidak cocok)
      // Error Network: Server tidak bisa dijangkau (Docker mati, IP salah, dll.)
      let msg = 'Tidak dapat terhubung ke server.';
      if (error.response && error.response.status === 422) {
        msg = 'Kredensial salah atau tidak ditemukan.';
      }
      Alert.alert('Gagal Login', msg);
    } finally {
      // Selalu matikan loading state setelah request selesai (sukses/gagal)
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.card}>
        {/* Header Aplikasi */}
        <Text style={styles.title}>Enterprise Inventory</Text>
        <Text style={styles.subtitle}>Login Staf Gudang</Text>

        {/* Input Email */}
        <View style={styles.inputContainer}>
          <Text style={styles.label}>Email</Text>
          <TextInput
            style={styles.input}
            placeholder="nama@email.com"
            placeholderTextColor="#64748b"
            value={email}
            onChangeText={setEmail}
            keyboardType="email-address"
            autoCapitalize="none"   // Penting: cegah huruf kapital otomatis di email
          />
        </View>

        {/* Input Password */}
        <View style={styles.inputContainer}>
          <Text style={styles.label}>Password</Text>
          <TextInput
            style={styles.input}
            placeholder="••••••••"
            placeholderTextColor="#64748b"
            value={password}
            onChangeText={setPassword}
            secureTextEntry     // Menyembunyikan karakter password
          />
        </View>

        {/* Tombol Login — menampilkan spinner saat proses */}
        <TouchableOpacity 
          style={styles.button} 
          onPress={handleLogin} 
          disabled={loading}      // Nonaktifkan tombol saat loading untuk mencegah double-submit
        >
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.buttonText}>Masuk</Text>
          )}
        </TouchableOpacity>
      </View>
    </View>
  );
}

// ============================================================
// StyleSheet
// ============================================================
// Menggunakan palet warna yang selaras dengan Web Dashboard (Filament dark theme)
// Warna utama: slate-900 (#0f172a), sky-500 (#0ea5e9)
const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#0f172a', // slate-900: Latar belakang gelap utama
    justifyContent: 'center',
    padding: 20,
  },
  card: {
    backgroundColor: 'rgba(30, 41, 59, 0.6)', // slate-800 semi-transparan: Efek glassmorphism
    padding: 24,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.05)', // Border sangat tipis untuk depth
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#f8fafc',   // slate-50: Teks terang
    textAlign: 'center',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 14,
    color: '#0ea5e9',   // sky-500: Warna brand utama
    textAlign: 'center',
    marginBottom: 32,
  },
  inputContainer: {
    marginBottom: 16,
  },
  label: {
    color: '#cbd5e1',   // slate-300: Label form
    marginBottom: 6,
    fontSize: 14,
    fontWeight: '500',
  },
  input: {
    backgroundColor: 'rgba(15, 23, 42, 0.6)', // slate-950 semi-transparan
    borderWidth: 1,
    borderColor: '#334155',  // slate-700: Border input
    borderRadius: 10,
    color: '#f8fafc',
    paddingHorizontal: 16,
    paddingVertical: 12,
    fontSize: 16,
  },
  button: {
    backgroundColor: '#0ea5e9', // sky-500: Warna CTA utama
    paddingVertical: 14,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 10,
  },
  buttonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: 'bold',
  }
});
