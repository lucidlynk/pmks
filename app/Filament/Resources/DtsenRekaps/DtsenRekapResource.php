<?php

namespace App\Filament\Resources\DtsenRekaps;

use App\Enums\UserRole;
use App\Filament\Resources\DtsenRekaps\Pages\CreateDtsenRekap;
use App\Filament\Resources\DtsenRekaps\Pages\ListDtsenRekaps;
use App\Filament\Resources\DtsenRekaps\Pages\ViewDtsenRekap;
use App\Filament\Resources\DtsenRekaps\Schemas\DtsenRekapForm;
use App\Filament\Resources\DtsenRekaps\RelationManagers\DetailsRelationManager;
use App\Filament\Resources\DtsenRekaps\Tables\DtsenRekapsTable;
use App\Models\DtsenRekap;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DtsenRekapResource extends Resource
{
    protected static ?string $model = DtsenRekap::class;
    protected static ?string $modelLabel = 'Rekap DTSEN';
    protected static ?string $pluralModelLabel = 'Rekap DTSEN';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::OPERATOR_DESA->value,
            UserRole::VERIFIKATOR->value,
        ]) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value) ?? false;
    }

    public static function getNavigationLabel(): string
    {
        return 'Rekap DTSEN';
    }

    public static function getNavigationGroup(): string
    {
        return 'Surat DTSEN';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return DtsenRekapForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DtsenRekapsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['uploader'])
            ->withCount('details');
    }

    public static function getRelations(): array
    {
        return [
            DetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDtsenRekaps::route('/'),
            'create' => CreateDtsenRekap::route('/create'),
            'view'   => ViewDtsenRekap::route('/{record}'),
        ];
    }
}
