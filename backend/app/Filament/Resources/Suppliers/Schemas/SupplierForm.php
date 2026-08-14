<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    /**
     * Form supplier mengikuti praktik standar sistem warehouse:
     * informasi perusahaan, kontak, identitas pajak (NPWP), ketentuan
     * pembayaran, catatan, dan status keaktifan.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label('Nama Perusahaan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: PT Sinar Jaya Elektronik'),
                TextInput::make('contact_person')
                    ->label('Kontak Person')
                    ->maxLength(255)
                    ->placeholder('Nama PIC yang bisa dihubungi'),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->placeholder('supplier@perusahaan.com'),
                TextInput::make('phone')
                    ->label('No. Telepon / WhatsApp')
                    ->tel()
                    ->regex('/^[0-9]+$/')
                    ->hintIcon('heroicon-m-information-circle', tooltip: 'Hanya boleh diisi angka')
                    ->extraInputAttributes([
                        'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"
                    ])
                    ->placeholder('Contoh: 081234567890'),
                TextInput::make('npwp')
                    ->label('NPWP (opsional)')
                    ->maxLength(30)
                    ->helperText('Nomor Pokok Wajib Pajak untuk keperluan administrasi')
                    ->placeholder('00.000.000.0-000.000'),
                Select::make('payment_terms')
                    ->label('Ketentuan Pembayaran')
                    ->options([
                        'cod'      => 'COD (Cash on Delivery)',
                        'net_7'    => 'Net 7 hari',
                        'net_14'   => 'Net 14 hari',
                        'net_30'   => 'Net 30 hari',
                        'net_60'   => 'Net 60 hari',
                    ])
                    ->placeholder('Pilih ketentuan pembayaran'),
                Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull()
                    ->rows(3)
                    ->placeholder('Alamat lengkap supplier / gudang'),
                Textarea::make('notes')
                    ->label('Catatan (opsional)')
                    ->columnSpanFull()
                    ->rows(2)
                    ->helperText('Catatan internal, misal: reputasi supplier, syarat khusus, dll.'),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->required()
                    ->default(true)
                    ->helperText('Supplier non-aktif tidak muncul di pilihan transaksi'),
            ]);
    }
}
