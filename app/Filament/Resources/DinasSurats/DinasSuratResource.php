<?php

namespace App\Filament\Resources\DinasSurats;

use App\Enums\UserRole;
use App\Filament\Resources\DinasSurats\Pages\CreateDinasSurat;
use App\Filament\Resources\DinasSurats\Pages\EditDinasSurat;
use App\Filament\Resources\DinasSurats\Pages\ListDinasSurats;
use App\Filament\Resources\DinasSurats\Pages\ViewDinasSurat;
use App\Filament\Resources\DinasSurats\Schemas\DinasSuratForm;
use App\Filament\Resources\DinasSurats\Tables\DinasSuratsTable;
use App\Models\DinasSurat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DinasSuratResource extends Resource
{
    protected static ?string $model = DinasSurat::class;
    protected static ?string $modelLabel = 'Surat Dinas';
    protected static ?string $pluralModelLabel = 'Surat Dinas';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value) ?? false;
    }

    public static function getNavigationLabel(): string
    {
        return 'Surat Dinas';
    }

    public static function getNavigationGroup(): string
    {
        return 'Informasi & Dokumen';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return DinasSuratForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DinasSuratsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDinasSurats::route('/'),
            'create' => CreateDinasSurat::route('/create'),
            'view'   => ViewDinasSurat::route('/{record}'),
            'edit'   => EditDinasSurat::route('/{record}/edit'),
        ];
    }
}
