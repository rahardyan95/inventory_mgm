<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Role')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Role')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->readOnly(fn (string $operation): bool => $operation === 'edit')
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? 'Nama role tidak dapat diubah setelah dibuat.'
                                : 'Contoh: supervisor, admin_gudang'),
                        Select::make('guard_name')
                            ->label('Guard')
                            ->required()
                            ->options(['web' => 'Web'])
                            ->default('web')
                            ->disabled()
                            ->dehydrated(),
                    ]),
                Section::make('Permissions')
                    ->description('Centang permission yang dimiliki role ini.')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('Daftar Permission')
                            ->relationship('permissions', 'name')
                            ->columns(2)
                            ->options(fn (): array => Permission::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->bulkToggleable(),
                    ]),
            ]);
    }
}
