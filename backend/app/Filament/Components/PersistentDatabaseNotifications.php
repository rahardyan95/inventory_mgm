<?php

namespace App\Filament\Components;

use Filament\Enums\DatabaseNotificationsPosition;
use Filament\Livewire\DatabaseNotifications;
use Illuminate\Contracts\View\View;

/**
 * Custom Database Notifications Component
 * 
 * Override komponen bawaan Filament agar notifikasi tidak otomatis hilang
 * (tidak di-mark as read) saat diklik satu-satu. Notifikasi hanya akan hilang
 * jika user menekan tombol "Bersihkan".
 * 
 * Meng-extends Filament\Livewire\DatabaseNotifications (bukan base di
 * Filament\Notifications\Livewire) agar trigger lonceng di topbar ikut
 * dirender dengan posisi & polling dari konfigurasi panel.
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
    public function markAllNotificationsAsReadAction(): \Filament\Actions\Action
    {
        return parent::markAllNotificationsAsReadAction()->hidden();
    }

    /**
     * Trigger lonceng modern di topbar (dengan badge + animasi pulse).
     */
    public function getTrigger(): ?View
    {
        return (($this->position ?? filament()->getDatabaseNotificationsPosition()) === DatabaseNotificationsPosition::Topbar)
            ? view('filament.components.topbar-database-notifications-trigger')
            : view('filament-panels::components.sidebar.database-notifications-trigger');
    }
}
