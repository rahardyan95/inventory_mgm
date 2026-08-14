<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * Widget: InventoryStatsWidget
 *
 * Menampilkan ringkasan statistik (angka-angka kunci) di bagian paling atas Dashboard.
 * Data grafik mini (sparkline) diambil dari data transaksi nyata, bukan angka statis,
 * sehingga selalu ter-update mengikuti aktivitas gudang.
 */
class InventoryStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    /**
     * Non-lazy: statistik langsung dirender saat halaman dimuat.
     */
    protected static bool $isLazy = false;

    /**
     * Data tren inbound/outbound 7 hari terakhir untuk sparkline.
     *
     * @return array{labels: array, inbound: array, outbound: array}
     */
    private function getSevenDayTrend(): array
    {
        $labels = [];
        $inbound = [];
        $outbound = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);

            $labels[] = $day->format('d/m');

            $inbound[] = TransactionItem::whereHas('transaction', fn ($q) => $q
                ->where('type', 'inbound')
                ->whereDate('transaction_date', $day))
                ->sum('quantity');

            $outbound[] = TransactionItem::whereHas('transaction', fn ($q) => $q
                ->where('type', 'outbound')
                ->whereDate('transaction_date', $day))
                ->sum('quantity');
        }

        return compact('labels', 'inbound', 'outbound');
    }

    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $trend = $this->getSevenDayTrend();

        $totalInbound = TransactionItem::whereHas('transaction', fn ($q) => $q
            ->where('type', 'inbound')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear))
            ->sum('quantity');

        $totalOutbound = TransactionItem::whereHas('transaction', fn ($q) => $q
            ->where('type', 'outbound')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear))
            ->sum('quantity');

        $monthLabel = Carbon::now()->translatedFormat('F Y');

        $stats = [
            Stat::make('Total Produk', Product::count())
                ->description('Jenis produk terdaftar')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->chart($trend['inbound'])
                ->extraAttributes([
                    'class' => 'fi-stats-card fi-stats-blue',
                ]),
            Stat::make('Stok Menipis', Product::lowStock()->count())
                ->description('Produk perlu restock')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->chart($trend['outbound'])
                ->extraAttributes([
                    'class' => 'fi-stats-card fi-stats-orange',
                ]),
        ];

        $user = auth()->user();
        if ($user && $user->hasAnyRole(['super_admin', 'manager'])) {
            $stats[] = Stat::make('Total Barang Masuk', $totalInbound)
                ->description("Bulan {$monthLabel}")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($trend['inbound'])
                ->extraAttributes([
                    'class' => 'fi-stats-card fi-stats-green',
                ]);
            $stats[] = Stat::make('Total Barang Keluar', $totalOutbound)
                ->description("Bulan {$monthLabel}")
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('primary')
                ->chart($trend['outbound'])
                ->extraAttributes([
                    'class' => 'fi-stats-card fi-stats-cyan',
                ]);
        }

        return $stats;
    }
}
