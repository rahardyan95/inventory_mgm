<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\Transaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    public static function form(Schema $schema): Schema
    {
        return TransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }

    // =========================================================
    // ANTI-FRAUD / ROLE-BASED ACCESS CONTROL
    // =========================================================

    /**
     * Semua role terdaftar boleh melihat menu transaksi.
     */
    public static function canViewAny(): bool
    {
        return true;
    }

    /**
     * Semua role boleh membuat transaksi.
     */
    public static function canCreate(): bool
    {
        return true;
    }

    /**
     * Edit transaksi:
     * - Manajer & Super Admin: boleh.
     * - Staff: hanya transaksi miliknya sendiri yang MASIH pending
     *   (belum disetujui). Jika sudah approved oleh manajer/super admin,
     *   tidak dapat diedit lagi.
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super_admin', 'manager'])) {
            return true;
        }

        if ($user->hasRole('staff')) {
            return $record->user_id === $user->id && $record->status !== 'approved';
        }

        return false;
    }

    /**
     * Staff TIDAK bisa menghapus transaksi — hanya Manajer & Super Admin.
     */
    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager']) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager']) ?? false;
    }
}
