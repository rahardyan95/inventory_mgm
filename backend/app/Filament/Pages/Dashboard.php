<?php

namespace App\Filament\Pages;

use App\Services\InventoryNotificationService;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Halaman Dashboard Kustom
 *
 * Melakukan overriding (penimpaan) pada halaman Dashboard bawaan Filament
 * agar kita bisa mengatur jumlah kolom grid menjadi 3 dan menampilkan
 * sapaan dinamis sesuai peran pengguna.
 *
 * Saat dashboard dibuka, notifikasi otomatis disinkronkan:
 * - Produk dengan stok menipis (low stock)
 * - Supplier yang berstatus non-aktif
 */
class Dashboard extends BaseDashboard
{
    /**
     * Judul halaman & label navigasi — "Dashboard" untuk semua role.
     */
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    /**
     * Menentukan jumlah kolom pada grid halaman dasbor.
     *
     * @return int|array Jumlah kolom (3)
     */
    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }

    /**
     * Sinkronkan notifikasi saat dashboard dimuat.
     */
    public function mount(): void
    {
        app(InventoryNotificationService::class)->notifyLowStockProducts();
        app(InventoryNotificationService::class)->notifyInactiveSuppliers();
    }

    public function getTitle(): string | Htmlable
    {
        return 'Dashboard';
    }

    /**
     * Sapaan mengikuti waktu Indonesia (WIB — Asia/Jakarta).
     */
    public function getHeading(): string | Htmlable
    {
        $user = auth()->user();

        $hour = (int) now('Asia/Jakarta')->format('H');

        $greeting = match (true) {
            $hour >= 4 && $hour < 11 => 'Selamat pagi',
            $hour >= 11 && $hour < 15 => 'Selamat siang',
            $hour >= 15 && $hour < 19 => 'Selamat sore',
            default                    => 'Selamat malam',
        };

        return "{$greeting}, {$user?->name}";
    }

    public function getSubheading(): string | Htmlable
    {
        return 'Ringkasan inventaris gudang secara real-time';
    }
}
