<?php

namespace App\Filament\Resources\FamilyCards;

use App\Enums\UserRole;
use App\Filament\Resources\FamilyCards\Pages\CreateFamilyCard;
use App\Filament\Resources\FamilyCards\Pages\EditFamilyCard;
use App\Filament\Resources\FamilyCards\Pages\ListFamilyCards;
use App\Filament\Resources\FamilyCards\Schemas\FamilyCardForm;
use App\Filament\Resources\FamilyCards\Tables\FamilyCardsTable;
use App\Models\FamilyCard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FamilyCardResource extends Resource
{
    protected static ?string $model = FamilyCard::class;
    protected static ?string $modelLabel = 'Kartu Keluarga';
    protected static ?string $pluralModelLabel = 'Kartu Keluarga';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    // Semua role kecuali Verifikator bisa akses
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::OPERATOR_DESA->value,
        ]) ?? false;
    }

    public static function getNavigationLabel(): string { return 'Kartu Keluarga'; }
    public static function getNavigationGroup(): string { return 'Data Penduduk'; }
    public static function getNavigationSort(): ?int { return 1; }

    public static function form(Schema $schema): Schema
    {
        return FamilyCardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FamilyCardsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();
        if ($user?->isOperatorDesa() && $user->village_id) {
            $query->where('village_id', $user->village_id);
        }
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListFamilyCards::route('/'),
            'create' => CreateFamilyCard::route('/create'),
            'edit'   => EditFamilyCard::route('/{record}/edit'),
        ];
    }
}
