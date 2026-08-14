<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Kelas TransactionsTable
 * 
 * Kelas ini bertanggung jawab untuk mengatur tampilan tabel pada halaman list Transaksi (Filament Resource).
 * Di sini kita mendefinisikan kolom-kolom apa saja yang akan ditampilkan, filter, serta aksi (edit, hapus).
 */
class TransactionsTable
{
    /**
     * Konfigurasi Tabel Transaksi
     * 
     * @param Table $table Objek tabel dari Filament
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference Number')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Jenis Transaksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inbound'    => 'success',
                        'outbound'   => 'danger',
                        'adjustment' => 'warning',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'inbound'    => 'Barang Masuk',
                        'outbound'   => 'Barang Keluar',
                        'adjustment' => 'Penyesuaian Stok',
                        default      => $state,
                    })
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        'cancelled'=> 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Disetujui',
                        'pending'  => 'Menunggu Persetujuan',
                        'rejected' => 'Ditolak',
                        'cancelled'=> 'Dibatalkan',
                        default    => $state,
                    })
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->date()
                    ->sortable(),
                // Kolom sekunder — tersembunyi secara default agar tabel pas di layar
                TextColumn::make('supplier.company_name')
                    ->label('Supplier')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis Transaksi')
                    ->options([
                        'inbound'    => 'Barang Masuk',
                        'outbound'   => 'Barang Keluar',
                        'adjustment' => 'Penyesuaian Stok',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled'=> 'Dibatalkan',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Transaction $record): bool => auth()->user()?->hasAnyRole(['super_admin', 'manager'])
                        || (auth()->user()?->hasRole('staff') && $record->user_id === auth()->id() && $record->status !== 'approved')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])
                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['super_admin', 'manager'])),
            ]);
    }
}
