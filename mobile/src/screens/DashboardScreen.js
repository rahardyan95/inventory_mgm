import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Alert, ScrollView, Dimensions } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import api, { setAuthToken } from '../services/api';
import { Feather } from '@expo/vector-icons';
import { LineChart } from 'react-native-chart-kit';

const screenWidth = Dimensions.get('window').width;

export default function DashboardScreen({ route, navigation }) {
  const { user } = route.params;
  const [stats, setStats] = useState({ total_products: 0, low_stock: 0, today_transactions: 0 });
  const [listData, setListData] = useState([]);
  const [chartData, setChartData] = useState(null);

  const role = user.roles?.[0] || 'staff';
  const isManager = role === 'super_admin' || role === 'manager';

  const [unreadCount, setUnreadCount] = useState(0);

  useEffect(() => {
    fetchStats();
    if (isManager) {
      fetchChartData();
      
      // Initial fetch for notifications
      fetchUnreadCount();
      
      // Set up polling for real-time notifications every 30 seconds
      const interval = setInterval(() => {
        fetchUnreadCount();
      }, 30000);
      
      return () => clearInterval(interval);
    } else {
      fetchInboundTransactions();
    }
  }, []);

  const fetchUnreadCount = async () => {
    try {
      const response = await api.get('/notifications');
      setUnreadCount(response.data.unread_count || 0);
    } catch (error) {
      console.log('Error fetching notifications count:', error);
    }
  };

  const fetchStats = async () => {
    try {
      const response = await api.get('/dashboard/stats');
      const data = response.data;
      setStats({
        total_products: data.total_products,
        today_transactions: data.today_inbound + data.today_outbound
      });
    } catch (error) {
      console.log('Error fetching stats:', error);
    }
  };

  const fetchChartData = async () => {
    try {
      const response = await api.get('/dashboard/chart');
      setChartData(response.data);
    } catch (error) {
      console.log('Error fetching chart data:', error);
    }
  };

  const fetchInboundTransactions = async () => {
    try {
      const response = await api.get('/transactions?type=inbound&per_page=5');
      setListData(response.data.data || []);
    } catch (error) {
      console.log('Error fetching inbound transactions:', error);
    }
  };

  const handleLogout = async () => {
    try {
      await api.post('/logout');
    } catch (e) {
      console.log('Logout error', e);
    } finally {
      setAuthToken(null);
      navigation.replace('Login');
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.container}>
        <View style={styles.header}>
          <View>
            <Text style={styles.greeting}>Halo, {user.name}</Text>
            <Text style={styles.role}>{user.roles?.[0] || 'Staf Gudang'}</Text>
          </View>
          <View style={{ flexDirection: 'row', gap: 10 }}>
            {isManager && (
              <TouchableOpacity onPress={() => navigation.navigate('Notifications')} style={styles.iconBtn} activeOpacity={0.6}>
                <Feather name="bell" size={20} color="#0ea5e9" />
                {unreadCount > 0 && (
                  <View style={styles.badge}>
                    <Text style={styles.badgeText}>{unreadCount > 99 ? '99+' : unreadCount}</Text>
                  </View>
                )}
              </TouchableOpacity>
            )}
            <TouchableOpacity onPress={handleLogout} style={styles.logoutBtn} activeOpacity={0.6}>
              <Feather name="log-out" size={20} color="#ef4444" />
            </TouchableOpacity>
          </View>
        </View>

        <View style={styles.statsContainer}>
          <View style={styles.statBox}>
            <Feather name="box" size={48} color="rgba(248, 250, 252, 0.05)" style={styles.statIcon} />
            <Text style={styles.statValue}>{stats.total_products}</Text>
            <Text style={styles.statLabel}>Total Produk</Text>
          </View>
          <View style={styles.statBox}>
            <Feather name="activity" size={48} color="rgba(248, 250, 252, 0.05)" style={styles.statIcon} />
            <Text style={styles.statValue}>{stats.today_transactions}</Text>
            <Text style={styles.statLabel}>Transk. Hari Ini</Text>
          </View>
        </View>

        <View style={styles.actionsContainer}>
          <Text style={styles.sectionTitle}>Aksi Cepat</Text>
          <TouchableOpacity 
            style={styles.scanButton}
            activeOpacity={0.75}
            onPress={() => navigation.navigate('Scanner')}
          >
            <View style={styles.scanButtonContent}>
              <Feather name="maximize" size={20} color="#fff" style={{marginRight: 8}} />
              <Text style={styles.scanButtonText}>Scan Barcode Barang</Text>
            </View>
          </TouchableOpacity>
        </View>

        {isManager ? (
          <View style={styles.chartContainer}>
            <Text style={styles.sectionTitle}>Grafik Transaksi (7 Hari)</Text>
            {chartData ? (
              <LineChart
                data={chartData}
                width={screenWidth - 40}
                height={220}
                chartConfig={{
                  backgroundColor: '#1e293b',
                  backgroundGradientFrom: '#1e293b',
                  backgroundGradientTo: '#1e293b',
                  decimalPlaces: 0,
                  color: (opacity = 1) => `rgba(255, 255, 255, ${opacity})`,
                  labelColor: (opacity = 1) => `rgba(255, 255, 255, ${opacity})`,
                  style: { borderRadius: 16 },
                  propsForDots: { r: '4', strokeWidth: '2', stroke: '#0ea5e9' }
                }}
                bezier
                style={{
                  marginVertical: 8,
                  borderRadius: 16
                }}
              />
            ) : (
              <Text style={styles.emptyText}>Memuat grafik...</Text>
            )}
          </View>
        ) : (
          <View style={styles.productsContainer}>
            <Text style={styles.sectionTitle}>Produk Masuk Terakhir</Text>
            {listData.length > 0 ? (
              listData.map((item, index) => (
                <View key={item.id || index} style={styles.productCard}>
                  <View style={{flexDirection: 'row', alignItems: 'center'}}>
                    <View style={{backgroundColor: 'rgba(14, 165, 233, 0.1)', padding: 10, borderRadius: 10, marginRight: 12}}>
                      <Feather name={item.transaction_code ? "file-text" : "box"} size={20} color="#0ea5e9" />
                    </View>
                    <View>
                      <Text style={styles.productName}>{item.transaction_code || item.name}</Text>
                      <Text style={styles.productSku}>{item.transaction_date || item.sku}</Text>
                    </View>
                  </View>
                  <View style={styles.stockBadge}>
                    <Text style={styles.stockText}>{item.total_items ? `${item.total_items} items` : `${item.current_stock} pcs`}</Text>
                  </View>
                </View>
              ))
            ) : (
              <Text style={styles.emptyText}>Belum ada transaksi.</Text>
            )}
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#0f172a',
  },
  container: {
    padding: 20,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 30,
    marginTop: 20,
  },
  greeting: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#f8fafc',
  },
  role: {
    color: '#0ea5e9',
    fontSize: 14,
    marginTop: 4,
    textTransform: 'capitalize',
  },
  logoutBtn: {
    backgroundColor: 'rgba(239, 68, 68, 0.1)',
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
    borderRadius: 20,
    borderWidth: 1,
    borderColor: 'rgba(239, 68, 68, 0.3)',
  },
  iconBtn: {
    backgroundColor: 'rgba(14, 165, 233, 0.1)',
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
    borderRadius: 20,
    borderWidth: 1,
    borderColor: 'rgba(14, 165, 233, 0.3)',
    position: 'relative',
  },
  badge: {
    position: 'absolute',
    top: -5,
    right: -5,
    backgroundColor: '#ef4444',
    borderRadius: 10,
    minWidth: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 4,
    borderWidth: 2,
    borderColor: '#0f172a',
  },
  badgeText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: 'bold',
  },
  statsContainer: {
    flexDirection: 'row',
    gap: 15,
    marginBottom: 30,
  },
  statBox: {
    flex: 1,
    backgroundColor: 'rgba(30, 41, 59, 0.6)',
    padding: 20,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.05)',
    alignItems: 'center',
    overflow: 'hidden',
    position: 'relative',
  },
  statIcon: {
    position: 'absolute',
    right: -10,
    bottom: -15,
  },
  statValue: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#f8fafc',
    marginBottom: 4,
    zIndex: 1,
  },
  statLabel: {
    fontSize: 12,
    color: '#94a3b8',
    zIndex: 1,
  },
  actionsContainer: {
    marginTop: 10,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#f8fafc',
    marginBottom: 15,
  },
  scanButton: {
    backgroundColor: '#0ea5e9',
    paddingVertical: 18,
    borderRadius: 12,
    alignItems: 'center',
    shadowColor: '#0ea5e9',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  scanButtonContent: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  scanButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  chartContainer: {
    marginTop: 30,
    marginBottom: 20,
    backgroundColor: 'rgba(30, 41, 59, 0.4)',
    padding: 16,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.05)',
  },
  productsContainer: {
    marginTop: 30,
    marginBottom: 20,
  },
  productCard: {
    backgroundColor: 'rgba(30, 41, 59, 0.4)',
    padding: 16,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.05)',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  productName: {
    color: '#f8fafc',
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 4,
  },
  productSku: {
    color: '#94a3b8',
    fontSize: 12,
  },
  stockBadge: {
    backgroundColor: 'rgba(14, 165, 233, 0.2)',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: 'rgba(14, 165, 233, 0.5)',
  },
  stockText: {
    color: '#38bdf8',
    fontWeight: 'bold',
    fontSize: 14,
  },
  emptyText: {
    color: '#94a3b8',
    textAlign: 'center',
    marginTop: 20,
    fontStyle: 'italic',
  }
});
