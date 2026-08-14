<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Anti-fraud: paksa data penting di sisi server sebelum disimpan.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // Nomor referensi selalu dibuat otomatis oleh sistem.
        $data['reference_number'] = Transaction::generateReferenceNumber($data['type'] ?? 'inbound');

        // "Dibuat Oleh" selalu mengikuti akun yang login — tidak bisa dipalsukan.
        $data['user_id'] = $user->id;

        if ($user->hasRole('staff')) {
            // Staff tidak boleh menunjuk approver / menentukan status.
            $data['approved_by'] = null;
            $data['approved_at'] = null;
            $data['status'] = 'pending';
        } else {
            // Manajer / Super Admin: approval otomatis atas nama akun sendiri.
            $data['approved_by'] = $user->id;
            $data['approved_at'] = $data['approved_at'] ?? now();
            $data['status'] = $data['status'] ?? 'pending';
        }

        return $data;
    }
}
