<?php

namespace App\Filament\Resources\KisRekaps;

use App\Enums\UserRole;
use App\Filament\Resources\KisRekaps\Pages\CreateKisRekap;
use App\Filament\Resources\KisRekaps\Pages\EditKisRekap;
use App\Filament\Resources\KisRekaps\Pages\ListKisRekaps;
use App\Filament\Resources\KisRekaps\Pages\ViewKisRekap;
use App\Filament\Resources\KisRekaps\Schemas\KisRekapForm;
use App\Filament\Resources\KisRekaps\Tables\KisRekapsTable;
use App\Models\KisRekap;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KisRekapResource extends Resource
{
    protected static ?string $model = KisRekap::class;
    protected static ?string $modelLabel = 'Rekap KIS';
    protected static ?string $pluralModelLabel = 'Rekap KIS';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public static function canAccess(): bool
    {
        return auth()->check();
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
        return 'Rekap KIS';
    }

    public static function getNavigationGroup(): string
    {
        return 'Data KIS / JKN';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return KisRekapForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KisRekapsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListKisRekaps::route('/'),
            'create' => CreateKisRekap::route('/create'),
            'view'   => ViewKisRekap::route('/{record}'),
            'edit'   => EditKisRekap::route('/{record}/edit'),
        ];
    }
}
