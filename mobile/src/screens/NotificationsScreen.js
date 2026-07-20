import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import api from '../services/api';
import { Feather } from '@expo/vector-icons';

export default function NotificationsScreen({ navigation }) {
  const [notifications, setNotifications] = useState([]);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    fetchNotifications();
  }, []);

  const fetchNotifications = async () => {
    try {
      const response = await api.get('/notifications');
      setNotifications(response.data.notifications || []);
    } catch (error) {
      console.log('Error fetching notifications:', error);
    } finally {
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchNotifications();
  };

  const markAllAsRead = async () => {
    try {
      await api.post('/notifications/read-all');
      fetchNotifications();
    } catch (error) {
      console.log('Error marking all as read:', error);
    }
  };

  const markAsRead = async (id) => {
    try {
      await api.post(`/notifications/${id}/read`);
      fetchNotifications();
    } catch (error) {
      console.log('Error marking as read:', error);
    }
  };

  const renderItem = ({ item }) => (
    <TouchableOpacity 
      style={[styles.notificationCard, !item.read && styles.unreadCard]}
      onPress={() => !item.read && markAsRead(item.id)}
    >
      <View style={styles.iconContainer}>
        <Feather name={item.icon?.replace('heroicon-o-', '').replace('heroicon-m-', '') || 'bell'} size={24} color={item.iconColor === 'warning' ? '#f59e0b' : item.iconColor === 'danger' ? '#ef4444' : '#0ea5e9'} />
      </View>
      <View style={styles.textContainer}>
        <Text style={[styles.title, !item.read && styles.unreadText]}>{item.title}</Text>
        <Text style={styles.body}>{item.body}</Text>
        <Text style={styles.date}>{new Date(item.created_at).toLocaleString('id-ID')}</Text>
      </View>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
          <Feather name="arrow-left" size={24} color="#f8fafc" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Notifikasi</Text>
        <TouchableOpacity onPress={markAllAsRead}>
          <Text style={styles.markAllText}>Baca Semua</Text>
        </TouchableOpacity>
      </View>

      <FlatList
        data={notifications}
        keyExtractor={(item) => item.id.toString()}
        renderItem={renderItem}
        contentContainerStyle={styles.listContainer}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#0ea5e9" />
        }
        ListEmptyComponent={
          <Text style={styles.emptyText}>Tidak ada notifikasi.</Text>
        }
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: '#0f172a' },
  header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', padding: 20, borderBottomWidth: 1, borderBottomColor: 'rgba(255,255,255,0.1)' },
  headerTitle: { fontSize: 18, fontWeight: 'bold', color: '#f8fafc' },
  backBtn: { padding: 5 },
  markAllText: { color: '#0ea5e9', fontSize: 14, fontWeight: '600' },
  listContainer: { padding: 15 },
  notificationCard: { flexDirection: 'row', backgroundColor: 'rgba(30, 41, 59, 0.4)', padding: 15, borderRadius: 12, marginBottom: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.05)' },
  unreadCard: { backgroundColor: 'rgba(14, 165, 233, 0.1)', borderColor: 'rgba(14, 165, 233, 0.3)' },
  iconContainer: { marginRight: 15, justifyContent: 'center' },
  textContainer: { flex: 1 },
  title: { fontSize: 16, fontWeight: '600', color: '#cbd5e1', marginBottom: 4 },
  unreadText: { color: '#f8fafc', fontWeight: 'bold' },
  body: { fontSize: 14, color: '#94a3b8', lineHeight: 20 },
  date: { fontSize: 12, color: '#64748b', marginTop: 8 },
  emptyText: { color: '#94a3b8', textAlign: 'center', marginTop: 40, fontStyle: 'italic' }
});
