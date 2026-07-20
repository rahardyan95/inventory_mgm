/**
 * ==========================================================
 * App.js — Entry Point Aplikasi Mobile
 * ==========================================================
 *
 * File utama yang dijalankan pertama kali saat aplikasi dibuka.
 * Mengatur:
 * 1. SafeAreaProvider — Context untuk safe area (notch, status bar)
 * 2. NavigationContainer — Container utama React Navigation
 * 3. Stack Navigator — Definisi semua rute/halaman aplikasi
 *
 * Struktur Navigasi:
 * ┌─────────────────────────────────────────┐
 * │           App (NavigationContainer)      │
 * │  ┌──────────┐   ┌───────────────────┐   │
 * │  │  Login   │──▶│    Dashboard      │   │
 * │  │ (default)│   │  (setelah login)  │   │
 * │  └──────────┘   └────────┬──────────┘   │
 * │                          │              │
 * │              ┌───────────┴──────────┐   │
 * │         ┌────▼────┐   ┌────────────▼┐  │
 * │         │ Scanner │──▶│ Transaction │  │
 * │         │ (kamera)│   │    Form     │  │
 * │         └─────────┘   └────────────┘  │
 * └─────────────────────────────────────────┘
 *
 * Akun yang bisa login (seeded dari database):
 * - admin@inventory.test / password   (Super Admin)
 * - manager@inventory.test / password (Manager)
 * - staff@inventory.test / password   (Staff Gudang)
 */

import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import ScannerScreen from './src/screens/ScannerScreen';
import LoginScreen from './src/screens/LoginScreen';
import DashboardScreen from './src/screens/DashboardScreen';
import TransactionFormScreen from './src/screens/TransactionFormScreen';
import NotificationsScreen from './src/screens/NotificationsScreen';

// Buat instance Stack Navigator untuk mengelola riwayat navigasi
const Stack = createNativeStackNavigator();

export default function App() {
  return (
    /*
     * SafeAreaProvider — Harus membungkus seluruh app.
     * Menyediakan context safe area (padding dari notch/status bar/home indicator)
     * agar konten tidak tertutup elemen sistem. Menggantikan SafeAreaView bawaan
     * react-native yang sudah deprecated.
     */
    <SafeAreaProvider>
      {/*
       * NavigationContainer — Container tunggal yang mengelola state navigasi.
       * Hanya boleh ada SATU NavigationContainer di seluruh aplikasi.
       */}
      <NavigationContainer>
        <Stack.Navigator 
          initialRouteName="Login"   // Halaman pertama yang ditampilkan
          screenOptions={{
            /* Konfigurasi header bar untuk semua screen (kecuali yang override) */
            headerStyle: {
              backgroundColor: '#1e293b',   // slate-800: Warna header gelap
            },
            headerTintColor: '#f8fafc',     // Warna teks & ikon back di header
            headerTitleStyle: {
              fontWeight: 'bold',
            },
          }}
        >
          {/* 
            * Screen: Login
            * Header disembunyikan karena desain login screen sudah custom
          */}
          <Stack.Screen 
            name="Login" 
            component={LoginScreen} 
            options={{ headerShown: false }}
          />

          {/*
            * Screen: Dashboard
            * Header disembunyikan karena header custom ada di dalam komponen
            * Menerima params: { user: { id, name, email, roles } }
          */}
          <Stack.Screen 
            name="Dashboard" 
            component={DashboardScreen} 
            options={{ headerShown: false }}
          />

          {/*
            * Screen: Scanner (Kamera Barcode)
            * Header ditampilkan dengan judul "Scan Barcode"
          */}
          <Stack.Screen 
            name="Scanner" 
            component={ScannerScreen} 
            options={{ title: 'Scan Barcode' }}
          />

          {/*
            * Screen: TransactionForm (Form Input Transaksi)
            * Header ditampilkan dengan judul "Form Transaksi"
            * Menerima params: { product: { id, name, sku, current_stock, ... } }
          */}
          <Stack.Screen 
            name="TransactionForm" 
            component={TransactionFormScreen} 
            options={{ title: 'Form Transaksi' }}
          />

          {/*
            * Screen: Notifications (Daftar Notifikasi)
            * Header disembunyikan karena menggunakan custom header
          */}
          <Stack.Screen 
            name="Notifications" 
            component={NotificationsScreen} 
            options={{ headerShown: false }}
          />
        </Stack.Navigator>
      </NavigationContainer>
    </SafeAreaProvider>
  );
}
