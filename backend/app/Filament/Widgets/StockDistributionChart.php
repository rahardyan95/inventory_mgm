<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;

/**
 * Widget: StockDistributionChart
 *
 * Widget berbentuk Doughnut Chart yang menampilkan perbandingan
 * total jumlah stok produk yang dikelompokkan berdasarkan kategorinya.
 * Ditampilkan untuk SEMUA peran dan auto-refresh setiap 30 detik.
 */
class StockDistributionChart extends ChartWidget
{
    /** Judul Grafik */
    protected ?string $heading = 'Distribusi Stok Kategori';
    
    /** Urutan tampil di dasbor */
    protected static ?int $sort = 3;
    
    /** Mengambil lebar 1 kolom grid (berdampingan dengan grafik tren) */
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    /**
     * Non-lazy: grafik langsung dirender saat halaman dimuat.
     */
    protected static bool $isLazy = false;

    /**
     * Auto-refresh grafik setiap 30 detik agar selalu ter-update.
     */
    protected ?string $pollingInterval = '30s';

    /**
     * Opsi grafik: proporsional & rapi di berbagai ukuran layar.
     */
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
                    'callbacks' => [
                        'label' => 'context => " " + context.label + ": " + context.parsed + " pcs"',
                    ],
                ],
            ],
        ];
    }

    /**
     * Memproses data stok, dikelompokkan dan dijumlahkan berdasar kategori.
     *
     * @return array Konfigurasi data untuk Doughnut Chart
     */
    protected function getData(): array
    {
        $categories = Category::withSum('products', 'current_stock')->get();
        
        $labels = [];
        $data = [];
        $colors = ['#10b981', '#3b82f6', '#f59e0b', '#06b6d4', '#8b5cf6', '#ec4899', '#84cc16', '#f97316'];
        
        foreach ($categories as $category) {
            $labels[] = $category->name;
            $data[] = $category->products_sum_current_stock ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Stok',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
