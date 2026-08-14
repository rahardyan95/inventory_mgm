<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Anti-fraud: kunci field yang tidak boleh diubah saat edit.
     * Nilai diambil dari record asli, bukan dari input user.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        // Field immutable: nomor referensi, pembuat, jenis, tanggal & approval.
        $data['reference_number']  = $this->record->reference_number;
        $data['user_id']           = $this->record->user_id;
        $data['type']              = $this->record->type;
        $data['transaction_date']  = $this->record->transaction_date;
        $data['approved_at']       = $this->record->approved_at;
        $data['approved_by']       = $this->record->approved_by;

        if ($user->hasRole('staff')) {
            // Staff tidak boleh mengubah status pada transaksi.
            $data['status'] = $this->record->status;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->hasAnyRole(['super_admin', 'manager'])),
        ];
    }
}
