<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;

/**
 * Widget: StockDistributionChart
 * 
 * Widget berbentuk Doughnut Chart yang menampilkan perbandingan
 * total jumlah stok produk yang dikelompokkan berdasarkan kategorinya.
 */
class StockDistributionChart extends ChartWidget
{
    /** Judul Grafik */
    protected ?string $heading = 'Distribusi Stok Kategori';
    
    /** Urutan tampil di dasbor */
    protected static ?int $sort = 3;
    
    /** Mengambil lebar 1 kolom grid (berdampingan dengan grafik tren) */
    protected int | string | array $columnSpan = 1;

    /**
     * Otomatisasi hak akses: hanya Manajer & Super Admin yang bisa melihat.
     */
    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->hasAnyRole(['super_admin', 'manager']);
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
        $colors = ['#10b981', '#3b82f6', '#f59e0b', '#06b6d4', '#8b5cf6', '#ec4899'];
        
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
