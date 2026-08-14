<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    /**
     * Daftar satuan (unit) umum di gudang.
     */
    private const UNITS = [
        'pcs'    => 'Pcs',
        'box'    => 'Box',
        'pack'   => 'Pack',
        'unit'   => 'Unit',
        'set'    => 'Set',
        'lusin'  => 'Lusin',
        'rim'    => 'Rim',
        'kg'     => 'Kilogram (kg)',
        'gram'   => 'Gram (g)',
        'liter'  => 'Liter (L)',
        'ml'     => 'Mililiter (mL)',
        'meter'  => 'Meter (m)',
        'botol'  => 'Botol',
        'sachet' => 'Sachet',
        'roll'   => 'Roll',
        'lembar' => 'Lembar',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->readOnly()
                    ->helperText('SKU dibuat otomatis berdasarkan kategori yang dipilih.')
                    ->placeholder('Pilih kategori terlebih dahulu'),
                TextInput::make('barcode')
                    ->label('Barcode')
                    ->required()
                    ->readOnly()
                    ->helperText('Barcode dibuat otomatis oleh sistem untuk pemindaian di aplikasi mobile.')
                    ->placeholder('Otomatis'),
                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($set, $state) {
                        if (! $state) {
                            $set('sku', null);
                            $set('barcode', null);
                            return;
                        }

                        $category = Category::find($state);
                        if (! $category) {
                            return;
                        }

                        // --- SKU otomatis mengikuti kategori ---
                        // Prefix diambil dari kategori (misal: Elektronik → ELK)
                        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $category->name), 0, 3));
                        if (strlen($prefix) < 3) {
                            $prefix = str_pad($prefix, 3, 'X');
                        }

                        // Cari nomor terkecil yang TERSEDIA (tidak dipakai produk AKTIF).
                        // Nomor dari produk yang sudah dihapus ikut tersedia kembali (reuse),
                        // sehingga SKU tidak melompat ke nomor selanjutnya.
                        $usedNumbers = Product::query()
                            ->where('category_id', $category->id)
                            ->whereNotNull('sku')
                            ->pluck('sku')
                            ->map(function (string $sku) use ($prefix): ?int {
                                if (! preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', $sku, $m)) {
                                    return null;
                                }

                                return (int) $m[1];
                            })
                            ->filter()
                            ->sort()
                            ->values();

                        $nextNumber = 1;
                        foreach ($usedNumbers as $usedNumber) {
                            if ($usedNumber === $nextNumber) {
                                $nextNumber++;
                                continue;
                            }

                            // Ada gap → gunakan nomor yang belum dipakai
                            if ($usedNumber > $nextNumber) {
                                break;
                            }
                        }

                        $sku = "{$prefix}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                        $set('sku', $sku);

                        // --- Barcode otomatis unik (EAN-13 style) ---
                        $set('barcode', static::generateUniqueBarcode());
                    }),
                Select::make('unit')
                    ->label('Satuan (Unit)')
                    ->required()
                    ->options(self::UNITS)
                    ->default('pcs')
                    ->searchable()
                    ->helperText('Pilih satuan barang, menyesuaikan jenis produk pada kategori.'),
                TextInput::make('purchase_price')
                    ->label('Harga Beli')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('IDR')
                    ->helperText('Hanya boleh diisi angka'),
                TextInput::make('selling_price')
                    ->label('Harga Jual')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('IDR')
                    ->helperText('Hanya boleh diisi angka'),
                TextInput::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('Hanya boleh diisi angka'),
                TextInput::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->required()
                    ->numeric()
                    ->default(10)
                    ->helperText('Hanya boleh diisi angka'),
                DatePicker::make('nearest_expiry_date')
                    ->label('Tanggal Kedaluwarsa')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->placeholder('DD/MM/YYYY'),
                FileUpload::make('image')
                    ->label('Gambar Produk')
                    ->image()
                    ->directory('products')
                    ->helperText('Unggah gambar produk. Integrasi kamera aplikasi mobile akan tersedia setelah versi web selesai.'),
            ]);
    }

    /**
     * Buat barcode numerik unik 13 digit (EAN-13 style).
     * Hanya produk aktif yang dihitung, sehingga barcode produk
     * yang dihapus dapat digunakan kembali.
     */
    private static function generateUniqueBarcode(): string
    {
        do {
            $barcode = '899' . now()->format('ymdHis') . Str::random(3) . rand(0, 9);
            $barcode = substr($barcode, 0, 13);
        } while (Product::query()->where('barcode', $barcode)->exists());

        return $barcode;
    }
}
