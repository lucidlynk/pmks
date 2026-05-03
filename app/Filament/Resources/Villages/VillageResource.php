<?php

namespace App\Filament\Resources\Villages;

use App\Enums\UserRole;
use App\Filament\Resources\Villages\Pages\CreateVillage;
use App\Filament\Resources\Villages\Pages\EditVillage;
use App\Filament\Resources\Villages\Pages\ListVillages;
use App\Filament\Resources\Villages\Schemas\VillageForm;
use App\Filament\Resources\Villages\Tables\VillagesTable;
use App\Models\Village;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VillageResource extends Resource
{
    protected static ?string $model = Village::class;
    protected static ?string $modelLabel = 'Desa';
    protected static ?string $pluralModelLabel = 'Desa / Kelurahan';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    // Hanya Admin Dinsos yang bisa kelola master data wilayah
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
        ]) ?? false;
    }

    public static function getNavigationLabel(): string { return 'Desa / Kelurahan'; }
    public static function getNavigationGroup(): string { return 'Master Data Wilayah'; }
    public static function getNavigationSort(): ?int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return VillageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VillagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListVillages::route('/'),
            'create' => CreateVillage::route('/create'),
            'edit'   => EditVillage::route('/{record}/edit'),
        ];
    }
}
