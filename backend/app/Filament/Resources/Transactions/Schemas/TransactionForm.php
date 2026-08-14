<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Supplier;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransactionForm
{
    /**
     * Label role dalam Bahasa Indonesia untuk format "Nama - Role".
     */
    private const ROLE_LABELS = [
        'super_admin' => 'Super Admin',
        'manager'     => 'Manajer',
        'staff'       => 'Staf Gudang',
    ];

    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $canApprove = $user && $user->hasAnyRole(['super_admin', 'manager']);

        return $schema
            ->components([
                TextInput::make('reference_number')
                    ->label('Nomor Referensi')
                    ->readOnly()
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated()
                    ->helperText('Nomor referensi dibuat otomatis oleh sistem dan tidak dapat diubah.')
                    ->placeholder('Otomatis'),
                Select::make('type')
                    ->label('Jenis Transaksi')
                    ->required()
                    ->options([
                        'inbound'    => 'Barang Masuk',
                        'outbound'   => 'Barang Keluar',
                        'adjustment' => 'Penyesuaian Stok',
                    ])
                    ->default('inbound')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated()
                    ->helperText(fn (string $operation): ?string => $operation === 'edit'
                        ? 'Jenis transaksi tidak dapat diubah setelah dibuat.'
                        : null),
                Select::make('user_id')
                    ->label('Dibuat Oleh')
                    ->relationship('user', 'name')
                    ->default(auth()->id())
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Otomatis mengikuti akun yang login.'),
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(fn (): array => Supplier::query()
                        ->where('is_active', true)
                        ->orderBy('company_name')
                        ->pluck('company_name', 'id')
                        ->all())
                    ->placeholder('Pilih supplier aktif')
                    ->searchable()
                    ->visible(fn (string $operation): bool => $operation === 'create'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DatePicker::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->placeholder('DD/MM/YYYY')
                    ->default(now())
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated()
                    ->helperText(fn (string $operation): ?string => $operation === 'edit'
                        ? 'Tanggal transaksi tidak dapat diubah setelah dibuat.'
                        : null),
                Select::make('status')
                    ->label('Status')
                    ->required()
                    ->default('pending')
                    ->options([
                        'pending'   => 'Menunggu Persetujuan',
                        'approved'  => 'Disetujui',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->visible($canApprove),
                Select::make('approved_by')
                    ->label('Disetujui Oleh')
                    ->options(fn (): array => User::query()
                        ->role(['manager', 'super_admin'])
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (User $approver): array => [
                            $approver->id => $approver->name . ' - ' . (self::ROLE_LABELS[$approver->getRoleNames()->first()] ?? $approver->getRoleNames()->first()),
                        ])
                        ->all())
                    ->default(auth()->id())
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Otomatis terisi akun yang sedang login (manajer / super admin).')
                    ->visible($canApprove),
                DatePicker::make('approved_at')
                    ->label('Tanggal Persetujuan')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->placeholder('DD/MM/YYYY')
                    ->default(now())
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated()
                    ->helperText(fn (string $operation): ?string => $operation === 'edit'
                        ? 'Tanggal persetujuan tidak dapat diubah setelah dibuat (waktu presisi tersimpan di database).'
                        : null)
                    ->visible($canApprove),
            ]);
    }
}
