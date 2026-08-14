<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Widget: MonthlyTransactionsChart
 *
 * Grafik garis tren transaksi (Barang Masuk vs Barang Keluar) selama
 * 12 bulan terakhir, per bulan. Ditampilkan untuk SEMUA peran dengan
 * referensi visual dashboard super admin / AdminLTE.
 *
 * Data otomatis ter-update setiap 30 detik.
 */
class MonthlyTransactionsChart extends ChartWidget
{
    protected ?string $heading = 'Tren Transaksi (12 Bulan Terakhir)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 2,
    ];

    /**
     * Non-lazy: grafik langsung dirender saat halaman dimuat.
     */
    protected static bool $isLazy = false;

    /**
     * Auto-refresh setiap 30 detik agar selalu ter-update.
     */
    protected ?string $pollingInterval = '30s';

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 12,
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ];
    }

    protected function getData(): array
    {
        $labels = [];
        $inbound = [];
        $outbound = [];

        // 12 bulan terakhir (termasuk bulan berjalan)
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');

            $inbound[] = TransactionItem::whereHas('transaction', fn ($q) => $q
                ->where('type', 'inbound')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month))
                ->sum('quantity');

            $outbound[] = TransactionItem::whereHas('transaction', fn ($q) => $q
                ->where('type', 'outbound')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month))
                ->sum('quantity');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Barang Masuk',
                    'data' => $inbound,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'borderWidth' => 2,
                    'pointRadius' => 3,
                ],
                [
                    'label' => 'Barang Keluar',
                    'data' => $outbound,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'borderWidth' => 2,
                    'pointRadius' => 3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
