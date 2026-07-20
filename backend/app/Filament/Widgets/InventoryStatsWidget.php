<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\TransactionItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * Widget: InventoryStatsWidget
 * 
 * Menampilkan ringkasan statistik (angka-angka kunci) di bagian paling atas Dasbor.
 * Berfungsi sebagai indikator cepat (KPI) untuk kondisi gudang.
 */
class InventoryStatsWidget extends BaseWidget
{
    /**
     * Urutan kemunculan widget di dasbor.
     */
    protected static ?int $sort = 1;
    
    /**
     * Lebar widget. 'full' berarti memakan 100% lebar grid.
     */
    protected int | string | array $columnSpan = 'full';

    /**
     * Menghasilkan array metrik (Stat) yang akan di-render.
     * Mengkalkulasi data Inbound dan Outbound khusus untuk bulan berjalan.
     * 
     * @return array
     */
    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totalInbound = TransactionItem::whereHas('transaction', function ($q) use ($currentMonth, $currentYear) {
            $q->where('type', 'inbound')
              ->whereMonth('transaction_date', $currentMonth)
              ->whereYear('transaction_date', $currentYear);
        })->sum('quantity');

        $totalOutbound = TransactionItem::whereHas('transaction', function ($q) use ($currentMonth, $currentYear) {
            $q->where('type', 'outbound')
              ->whereMonth('transaction_date', $currentMonth)
              ->whereYear('transaction_date', $currentYear);
        })->sum('quantity');

        $stats = [
            Stat::make('Total Produk', Product::count())
                ->description('Jumlah jenis produk')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->extraAttributes([
                    'class' => 'bg-blue-500 text-white',
                ]),
            Stat::make('Stok Menipis', Product::lowStock()->count())
                ->description('Produk butuh restock')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->chart([17, 16, 14, 15, 14, 13, 12])
                ->extraAttributes([
                    'class' => 'bg-orange-500 text-white',
                ]),
        ];

        $user = auth()->user();
        if ($user && $user->hasAnyRole(['super_admin', 'manager'])) {
            $stats[] = Stat::make('Total Inbound', $totalInbound)
                ->description('Barang masuk bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->extraAttributes([
                    'class' => 'bg-emerald-500 text-white',
                ]);
            $stats[] = Stat::make('Total Outbound', $totalOutbound)
                ->description('Barang keluar bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('primary')
                ->chart([1, 4, 2, 10, 5, 4, 15])
                ->extraAttributes([
                    'class' => 'bg-cyan-500 text-white',
                ]);
        }

        return $stats;
    }
}
