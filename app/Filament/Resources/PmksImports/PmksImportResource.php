<?php

namespace App\Filament\Resources\PmksImports;

use App\Enums\UserRole;
use App\Filament\Resources\PmksImports\Pages\CreatePmksImport;
use App\Filament\Resources\PmksImports\Pages\ListPmksImports;
use App\Filament\Resources\PmksImports\Pages\ViewPmksImport;
use App\Filament\Resources\PmksImports\Schemas\PmksImportForm;
use App\Filament\Resources\PmksImports\Tables\PmksImportsTable;
use App\Models\PmksImport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PmksImportResource extends Resource
{
    protected static ?string $model = PmksImport::class;
    protected static ?string $modelLabel = 'Import PMKS';
    protected static ?string $pluralModelLabel = 'Import PMKS';
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
        return 'Import PMKS';
    }

    public static function getNavigationGroup(): string
    {
        return 'Pengajuan PMKS & PSKS';
    }

    public static function getNavigationSort(): ?int
    {
        return 20;
    }

    public static function form(Schema $schema): Schema
    {
        return PmksImportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PmksImportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPmksImports::route('/'),
            'create' => CreatePmksImport::route('/create'),
            'view'   => ViewPmksImport::route('/{record}'),
        ];
    }
}
