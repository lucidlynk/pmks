<?php

namespace App\Filament\Resources\PsksImports;

use App\Enums\UserRole;
use App\Filament\Resources\PsksImports\Pages\CreatePsksImport;
use App\Filament\Resources\PsksImports\Pages\ListPsksImports;
use App\Filament\Resources\PsksImports\Pages\ViewPsksImport;
use App\Filament\Resources\PsksImports\Schemas\PsksImportForm;
use App\Filament\Resources\PsksImports\Tables\PsksImportsTable;
use App\Models\PsksImport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PsksImportResource extends Resource
{
    protected static ?string $model = PsksImport::class;
    protected static ?string $modelLabel = 'Import PSKS';
    protected static ?string $pluralModelLabel = 'Import PSKS';
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
        return 'Import PSKS';
    }

    public static function getNavigationGroup(): string
    {
        return 'Pengajuan PMKS & PSKS';
    }

    public static function getNavigationSort(): ?int
    {
        return 21;
    }

    public static function form(Schema $schema): Schema
    {
        return PsksImportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PsksImportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPsksImports::route('/'),
            'create' => CreatePsksImport::route('/create'),
            'view'   => ViewPsksImport::route('/{record}'),
        ];
    }
}
