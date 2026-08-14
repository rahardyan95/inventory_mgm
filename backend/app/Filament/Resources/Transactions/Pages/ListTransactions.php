<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * Anti-fraud: Staff hanya melihat transaksi yang ia buat sendiri.
     */
    protected function getTableQuery(): Builder
    {
        $query = TransactionResource::getEloquentQuery();

        if (auth()->user()?->hasRole('staff')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }
}
