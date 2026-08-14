<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Produk lama tanpa barcode otomatis diberi barcode unik saat diedit,
     * agar tetap dapat dipindai di aplikasi mobile.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (blank($data['barcode'] ?? null)) {
            $data['barcode'] = static::generateUniqueBarcode();
        }

        return $data;
    }

    /**
     * Barcode unik 13 digit; hanya produk aktif yang dihitung agar
     * barcode dari produk yang dihapus dapat digunakan kembali.
     */
    private static function generateUniqueBarcode(): string
    {
        do {
            $barcode = '899' . now()->format('ymdHis') . Str::random(3) . rand(0, 9);
            $barcode = substr($barcode, 0, 13);
        } while (Product::query()->where('barcode', $barcode)->exists());

        return $barcode;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
