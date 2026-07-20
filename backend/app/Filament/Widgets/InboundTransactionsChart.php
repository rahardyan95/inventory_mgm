<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Filament\Widgets\ChartWidget;

/**
 * Widget: InboundTransactionsChart
 * 
 * Menampilkan grafik garis (Line Chart) tren barang masuk (inbound) 
 * selama 3 tahun terakhir (Tahun ini, tahun lalu, 2 tahun lalu) per bulan.
 */
class InboundTransactionsChart extends ChartWidget
{
    /** Judul Grafik */
    protected ?string $heading = 'Barang Masuk per Bulan (3 Tahun Terakhir)';
    
    /** Urutan tampil di dasbor */
    protected static ?int $sort = 2;
    
    /** Lebar widget, menggunakan 2 kolom agar dominan */
    protected int | string | array $columnSpan = 2;

    /**
     * Otomatisasi hak akses: hanya Manajer & Super Admin yang bisa melihat.
     */
    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->hasAnyRole(['super_admin', 'manager']);
    }

    /**
     * Menyiapkan data 3 garis (datasets) yang akan digambar pada chart.
     * Melakukan iterasi 3 tahun ke belakang, mengambil total transaksi inbound.
     * 
     * @return array Konfigurasi dataset untuk Line Chart
     */
    protected function getData(): array
    {
        $currentYear = now()->year;
        $datasets = [];
        $years = [$currentYear, $currentYear - 1, $currentYear - 2];
        $colors = ['#10b981', '#3b82f6', '#f59e0b']; // Hijau, Biru, Oranye
        
        foreach ($years as $index => $year) {
            $monthlyData = array_fill(1, 12, 0);

            $items = TransactionItem::with('transaction')
                ->whereHas('transaction', function ($query) use ($year) {
                    $query->where('type', 'inbound')
                          ->whereYear('transaction_date', $year);
                })
                ->get();
                
            foreach ($items as $item) {
                if ($item->transaction && $item->transaction->transaction_date) {
                    $month = $item->transaction->transaction_date->month;
                    $monthlyData[$month] += $item->quantity;
                }
            }

            $datasets[] = [
                'label' => "Tahun {$year}",
                'data' => array_values($monthlyData),
                'borderColor' => $colors[$index],
                'backgroundColor' => 'transparent',
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
