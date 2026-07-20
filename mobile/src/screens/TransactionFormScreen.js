import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, Alert, ActivityIndicator } from 'react-native';
import api from '../services/api';
import { Feather } from '@expo/vector-icons';

export default function TransactionFormScreen({ route, navigation }) {
  const { product } = route.params;
  const [type, setType] = useState('in'); // 'in' or 'out'
  const [quantity, setQuantity] = useState('');
  const [notes, setNotes] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async () => {
    if (!quantity || isNaN(quantity) || parseInt(quantity) <= 0) {
      Alert.alert('Error', 'Jumlah barang harus lebih dari 0.');
      return;
    }

    setLoading(true);
    try {
      await api.post('/transactions', {
        type: type === 'in' ? 'inbound' : 'outbound',
        notes: notes,
        items: [
          {
            product_id: product.id,
            quantity: parseInt(quantity),
          }
        ]
      });

      Alert.alert('Sukses', 'Transaksi berhasil disimpan!', [
        { text: 'OK', onPress: () => navigation.navigate('Dashboard') }
      ]);
    } catch (error) {
      const msg = error.response?.data?.message || 'Gagal menyimpan transaksi.';
      Alert.alert('Error', msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Buat Transaksi Baru</Text>
      
      <View style={styles.productCard}>
        <Text style={styles.productName}>{product.name}</Text>
        <Text style={styles.productSku}>SKU: {product.sku}</Text>
        <Text style={styles.productStock}>Stok Saat Ini: {product.current_stock}</Text>
      </View>

      <Text style={styles.label}>Jenis Transaksi</Text>
      <View style={styles.typeContainer}>
        <TouchableOpacity 
          style={[styles.typeButton, type === 'in' && styles.typeButtonActiveIn]}
          onPress={() => setType('in')}
        >
          <Feather name="arrow-down-left" size={18} color={type === 'in' ? '#fff' : '#cbd5e1'} style={{marginBottom: 4}} />
          <Text style={[styles.typeText, type === 'in' && styles.typeTextActive]}>Barang Masuk</Text>
        </TouchableOpacity>
        
        <TouchableOpacity 
          style={[styles.typeButton, type === 'out' && styles.typeButtonActiveOut]}
          onPress={() => setType('out')}
        >
          <Feather name="arrow-up-right" size={18} color={type === 'out' ? '#fff' : '#cbd5e1'} style={{marginBottom: 4}} />
          <Text style={[styles.typeText, type === 'out' && styles.typeTextActive]}>Barang Keluar</Text>
        </TouchableOpacity>
      </View>

      <Text style={styles.label}>Jumlah Barang</Text>
      <TextInput
        style={styles.input}
        keyboardType="numeric"
        placeholder="0"
        placeholderTextColor="#64748b"
        value={quantity}
        onChangeText={setQuantity}
      />

      <Text style={styles.label}>Catatan (Opsional)</Text>
      <TextInput
        style={[styles.input, styles.textArea]}
        placeholder="Kondisi barang, referensi, dll..."
        placeholderTextColor="#64748b"
        multiline
        numberOfLines={3}
        value={notes}
        onChangeText={setNotes}
      />

      <TouchableOpacity 
        style={styles.submitButton}
        onPress={handleSubmit}
        disabled={loading}
      >
        {loading ? <ActivityIndicator color="#fff" /> : (
          <View style={{flexDirection: 'row', alignItems: 'center'}}>
            <Feather name="save" size={20} color="#fff" style={{marginRight: 8}} />
            <Text style={styles.submitButtonText}>Simpan Transaksi</Text>
          </View>
        )}
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    backgroundColor: '#0f172a',
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#f8fafc',
    marginBottom: 20,
  },
  productCard: {
    backgroundColor: 'rgba(30, 41, 59, 0.6)',
    padding: 16,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.05)',
    marginBottom: 24,
  },
  productName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#f8fafc',
  },
  productSku: {
    color: '#94a3b8',
    marginTop: 4,
  },
  productStock: {
    color: '#0ea5e9',
    fontWeight: '600',
    marginTop: 8,
  },
  label: {
    color: '#cbd5e1',
    marginBottom: 8,
    fontWeight: '500',
  },
  typeContainer: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 20,
  },
  typeButton: {
    flex: 1,
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#334155',
    alignItems: 'center',
  },
  typeButtonActiveIn: {
    backgroundColor: '#0ea5e9',
    borderColor: '#0ea5e9',
  },
  typeButtonActiveOut: {
    backgroundColor: '#ef4444',
    borderColor: '#ef4444',
  },
  typeText: {
    color: '#cbd5e1',
    fontWeight: '600',
  },
  typeTextActive: {
    color: '#fff',
  },
  input: {
    backgroundColor: 'rgba(15, 23, 42, 0.6)',
    borderWidth: 1,
    borderColor: '#334155',
    borderRadius: 10,
    color: '#f8fafc',
    paddingHorizontal: 16,
    paddingVertical: 12,
    fontSize: 16,
    marginBottom: 20,
  },
  textArea: {
    height: 100,
    textAlignVertical: 'top',
  },
  submitButton: {
    backgroundColor: '#10b981',
    paddingVertical: 16,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 10,
  },
  submitButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  }
});
