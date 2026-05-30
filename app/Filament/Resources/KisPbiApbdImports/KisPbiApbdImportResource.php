<?php

namespace App\Filament\Resources\KisPbiApbdImports;

use App\Enums\UserRole;
use App\Filament\Resources\KisPbiApbdImports\Pages\CreateKisPbiApbdImport;
use App\Filament\Resources\KisPbiApbdImports\Pages\ListKisPbiApbdImports;
use App\Filament\Resources\KisPbiApbdImports\Pages\ViewKisPbiApbdImport;
use App\Filament\Resources\KisPbiApbdImports\Schemas\KisPbiApbdImportForm;
use App\Filament\Resources\KisPbiApbdImports\Tables\KisPbiApbdImportsTable;
use App\Models\KisPbiApbdImport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KisPbiApbdImportResource extends Resource
{
    protected static ?string $model = KisPbiApbdImport::class;
    protected static ?string $modelLabel = 'Import PBI APBD';
    protected static ?string $pluralModelLabel = 'Import PBI APBD';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::VERIFIKATOR->value,
        ]) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
        ]) ?? false;
    }

    public static function getNavigationLabel(): string
    {
        return 'Import PBI APBD';
    }

    public static function getNavigationGroup(): string
    {
        return 'Data KIS / JKN';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return KisPbiApbdImportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KisPbiApbdImportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListKisPbiApbdImports::route('/'),
            'create' => CreateKisPbiApbdImport::route('/create'),
            'view'   => ViewKisPbiApbdImport::route('/{record}'),
        ];
    }
}
