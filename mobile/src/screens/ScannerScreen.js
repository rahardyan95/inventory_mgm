/**
 * ==========================================================
 * Screen: ScannerScreen
 * ==========================================================
 *
 * Halaman pemindai barcode menggunakan kamera perangkat.
 * Menggunakan expo-camera (CameraView) untuk memindai barcode produk.
 *
 * Alur Kerja:
 * 1. Saat screen dibuka, app meminta izin akses kamera ke sistem Android/iOS
 * 2. Kamera aktif dan siap memindai barcode jenis: QR, EAN-13, EAN-8, PDF417
 * 3. Saat barcode terdeteksi, `handleBarCodeScanned` dipanggil dengan kode yang terbaca
 * 4. App mengirim kode ke API backend (GET /api/products/barcode/:code)
 * 5. Jika produk ditemukan → tampilkan dialog konfirmasi → navigasi ke TransactionForm
 * 6. Jika tidak ditemukan → tampilkan error dialog
 *
 * Catatan Penting:
 * - `scanned` state digunakan untuk mencegah trigger berkali-kali saat barcode terbaca
 * - Di emulator Android, kamera mungkin tidak bisa scan barcode fisik
 *   (gunakan kamera virtual emulator atau tes di HP asli)
 */

import React, { useState, useEffect } from 'react';
import { StyleSheet, Text, View, Button, Alert } from 'react-native';
import { Camera, CameraView } from 'expo-camera';
import api from '../services/api';
import { Feather } from '@expo/vector-icons';

export default function ScannerScreen({ navigation }) {
  // State izin kamera: null = belum dicek, true = diizinkan, false = ditolak
  const [hasPermission, setHasPermission] = useState(null);
  // State kunci scanner: true = sedang memproses (cegah scan ganda), false = siap scan
  const [scanned, setScanned] = useState(false);

  /**
   * Meminta izin akses kamera ke sistem operasi saat komponen pertama dimuat.
   * Ini wajib dilakukan sebelum CameraView bisa digunakan.
   */
  useEffect(() => {
    const getCameraPermissions = async () => {
      const { status } = await Camera.requestCameraPermissionsAsync();
      setHasPermission(status === 'granted');
    };
    getCameraPermissions();
  }, []);

  /**
   * Callback saat barcode berhasil terdeteksi oleh CameraView.
   * Langsung mengunci scanner dan mencari produk ke Backend API.
   *
   * @param {string} type  - Tipe barcode (qr, ean13, dll.)
   * @param {string} data  - Nilai/kode yang terbaca dari barcode
   */
  const handleBarCodeScanned = async ({ type, data }) => {
    // Kunci scanner agar tidak berkedip baca berkali-kali selama proses API berlangsung
    setScanned(true);
    
    try {
      // Kirim kode barcode ke endpoint Laravel untuk dicari di database
      const response = await api.get(`/products/barcode/${data}`);
      const product  = response.data;
      
      Alert.alert(
        'Produk Ditemukan! ✅',
        `Nama: ${product.name}\nStok Saat Ini: ${product.current_stock} pcs`,
        [
          { 
            text: 'Proses Transaksi', 
            onPress: () => {
              // Bawa data produk ke TransactionFormScreen untuk diproses
              navigation.navigate('TransactionForm', { product });
              setScanned(false); // Buka kembali scanner untuk scan berikutnya
            }
          },
          { 
            text: 'Tutup', 
            onPress: () => setScanned(false), 
            style: 'cancel' 
          }
        ]
      );
    } catch (error) {
      if (error.response && error.response.status === 404) {
        // Produk tidak ditemukan di database → tampilkan pesan informatif
        Alert.alert('Produk Tidak Ditemukan', `Barcode: ${data}\nProduk tidak ada di database.`);
      } else {
        // Masalah koneksi jaringan (Docker mati, IP salah, dll.)
        Alert.alert('Error Koneksi', 'Tidak dapat terhubung ke server database. Pastikan backend berjalan.');
      }
      // Buka kembali scanner otomatis setelah 2 detik untuk percobaan berikutnya
      setTimeout(() => setScanned(false), 2000);
    }
  };

  // ============================================================
  // Render kondisional berdasarkan status izin kamera
  // ============================================================

  // Masih menunggu respons dari sistem (dialog izin belum muncul/dijawab)
  if (hasPermission === null) {
    return (
      <View style={styles.container}>
        <Text style={styles.textWhite}>Meminta izin akses kamera...</Text>
      </View>
    );
  }

  // User menolak izin kamera
  if (hasPermission === false) {
    return (
      <View style={styles.container}>
        <Feather name="camera-off" size={48} color="#ef4444" style={{ marginBottom: 16 }} />
        <Text style={styles.textWhite}>Akses kamera ditolak.</Text>
        <Text style={{ color: '#94a3b8', textAlign: 'center', marginTop: 8 }}>
          Aktifkan izin kamera di Pengaturan untuk menggunakan fitur scan.
        </Text>
      </View>
    );
  }

  // Tampilan utama scanner
  return (
    <View style={styles.container}>
      {/* Instruksi untuk user */}
      <Text style={styles.title}>Arahkan Kamera ke Barcode</Text>

      {/* Area kamera dengan overlay ikon targeting */}
      <View style={styles.scannerContainer}>
        <CameraView
          onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}  // undefined = scanner terkunci
          barcodeScannerSettings={{
            barcodeTypes: ["qr", "ean13", "ean8", "pdf417"],  // Format barcode yang didukung
          }}
          style={StyleSheet.absoluteFillObject}  // Isi penuh container
        />
        {/* Ikon overlay sebagai panduan visual untuk membantu user membidik barcode */}
        <View style={styles.overlayIcon}>
          <Feather name="maximize" size={80} color="rgba(14, 165, 233, 0.5)" />
        </View>
      </View>

      {/* Tombol scan ulang — muncul hanya setelah barcode terbaca */}
      {scanned && (
        <Button 
          title="Tap untuk Scan Lagi" 
          onPress={() => setScanned(false)} 
          color="#0ea5e9" 
        />
      )}
    </View>
  );
}

// ============================================================
// StyleSheet
// ============================================================
const styles = StyleSheet.create({
  container: {
    flex: 1,
    flexDirection: 'column',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#0f172a',  // slate-900: latar belakang gelap
  },
  textWhite: {
    color: '#f8fafc',
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 20,
    color: '#f8fafc',
  },
  scannerContainer: {
    width: 300,
    height: 300,
    overflow: 'hidden',
    borderRadius: 15,
    borderWidth: 2,
    borderColor: '#0ea5e9',   // Bingkai biru sky-500
    marginBottom: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  overlayIcon: {
    position: 'absolute',  // Melayang di atas kamera
    zIndex: 2,
  }
});
