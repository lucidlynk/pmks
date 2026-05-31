<?php

namespace App\Filament\Resources\BansosImports;

use App\Enums\UserRole;
use App\Filament\Resources\BansosImports\Pages\CreateBansosImport;
use App\Filament\Resources\BansosImports\Pages\ListBansosImports;
use App\Filament\Resources\BansosImports\Pages\ViewBansosImport;
use App\Filament\Resources\BansosImports\Schemas\BansosImportForm;
use App\Filament\Resources\BansosImports\Tables\BansosImportsTable;
use App\Models\BansosImport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BansosImportResource extends Resource
{
    protected static ?string $model = BansosImport::class;
    protected static ?string $modelLabel = 'Import Bansos';
    protected static ?string $pluralModelLabel = 'Import Bansos';
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
        return 'Import Bansos';
    }

    public static function getNavigationGroup(): string
    {
        return 'Data Bansos';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return BansosImportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BansosImportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBansosImports::route('/'),
            'create' => CreateBansosImport::route('/create'),
            'view'   => ViewBansosImport::route('/{record}'),
        ];
    }
}
