<?php

namespace App\Filament\Components;

use Filament\Notifications\Livewire\DatabaseNotifications;
use Filament\Actions\Action;

/**
 * Custom Database Notifications Component
 * 
 * Override komponen bawaan Filament agar notifikasi tidak otomatis hilang
 * (tidak di-mark as read) saat diklik satu-satu. Notifikasi hanya akan hilang
 * jika user menekan tombol "Bersihkan".
 */
class PersistentDatabaseNotifications extends DatabaseNotifications
{
    /**
     * Override method markNotificationAsRead.
     * Dikosongkan agar ketika notifikasi diklik, tidak ditandai sebagai dibaca.
     */
    public function markNotificationAsRead(string $id): void
    {
        // Sengaja dibiarkan kosong agar notifikasi tetap berstatus "unread"
        // sehingga tidak menghilang dari modal notifikasi saat diklik.
    }

    /**
     * Override tombol "Tandai semua sudah dibaca".
     * Disembunyikan agar user menggunakan tombol "Bersihkan" (clearNotificationsAction) 
     * jika ingin menghilangkan notifikasi.
     */
    public function markAllNotificationsAsReadAction(): Action
    {
        return parent::markAllNotificationsAsReadAction()->hidden();
    }
}
