<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Halaman Dashboard Kustom
 * 
 * Melakukan overriding (penimpaan) pada halaman Dashboard bawaan Filament
 * agar kita bisa mengatur jumlah kolom grid menjadi 3.
 * Hal ini diperlukan untuk menyusun tata letak grafik (2 kolom) dan donat (1 kolom) sejajar.
 */
class Dashboard extends BaseDashboard
{
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
}
