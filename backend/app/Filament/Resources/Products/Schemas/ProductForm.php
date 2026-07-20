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

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('barcode'),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($set, $state) {
                        if (! $state) {
                            $set('sku', null);
                            return;
                        }
                        
                        $category = Category::find($state);
                        if (! $category) {
                            return;
                        }

                        $latestProduct = Product::where('category_id', $category->id)->orderBy('id', 'desc')->first();
                        
                        if ($latestProduct && preg_match('/^([A-Z]+)-(\d+)$/', $latestProduct->sku, $matches)) {
                            $prefix = $matches[1];
                            $number = (int)$matches[2] + 1;
                            $sequence = str_pad($number, 3, '0', STR_PAD_LEFT);
                            $set('sku', "{$prefix}-{$sequence}");
                        } else {
                            // Fallback if no product exists in this category yet
                            $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $category->name), 0, 3));
                            if (strlen($prefix) < 3) {
                                $prefix = str_pad($prefix, 3, 'X');
                            }
                            $set('sku', "{$prefix}-001");
                        }
                    }),
                TextInput::make('unit')
                    ->required()
                    ->default('pcs'),
                TextInput::make('purchase_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('IDR')
                    ->helperText('Hanya boleh diisi angka'),
                TextInput::make('selling_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('IDR')
                    ->helperText('Hanya boleh diisi angka'),
                TextInput::make('current_stock')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('Hanya boleh diisi angka'),
                TextInput::make('minimum_stock')
                    ->required()
                    ->numeric()
                    ->default(10)
                    ->helperText('Hanya boleh diisi angka'),
                DatePicker::make('nearest_expiry_date')
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                FileUpload::make('image')
                    ->image(),
            ]);
    }
}
