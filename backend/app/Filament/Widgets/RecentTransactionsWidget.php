<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Widget: RecentTransactionsWidget
 * 
 * Widget berbentuk tabel ini berfungsi untuk menampilkan transaksi gudang terakhir.
 * Berguna agar staf dan manajer dapat melihat riwayat aktivitas terbaru dengan cepat.
 */
class RecentTransactionsWidget extends BaseWidget
{
    /** Urutan widget (paling bawah di dasbor) */
    protected static ?int $sort = 4;
    
    /** Mengambil lebar penuh (3 kolom) */
    protected int | string | array $columnSpan = 'full';
    
    /**
     * Judul yang akan ditampilkan di header tabel.
     */
    protected function getTableHeading(): string
    {
        return 'Transaksi Terkini';
    }

    /**
     * Konfigurasi tabel, termasuk kueri data dasar dan pembentukan kolom.
     * Jika role adalah staff, data difilter hanya milik staff tersebut.
     * 
     * @param Table $table Objek tabel Filament
     * @return Table
     */
    public function table(Table $table): Table
    {
        $user = auth()->user();
        
        return $table
            ->query(
                Transaction::query()
                    ->when($user && $user->hasRole('staff'), function (Builder $query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->latest('created_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('ID Transaksi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inbound' => 'success',
                        'outbound' => 'warning',
                        'adjustment' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed', 'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
