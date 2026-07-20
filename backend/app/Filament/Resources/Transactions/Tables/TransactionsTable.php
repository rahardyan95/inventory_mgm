<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
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
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference Number')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable(),
                // Mengambil nama supplier melalui relasi, bukan sekadar ID
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('transaction_date')
                    ->label('Transaction Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                // Mengambil nama manager (approver) dari relasi approver() di model Transaction
                TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
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
                // Tambahkan filter spesifik di sini (misal filter berdasarkan status/tipe)
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

