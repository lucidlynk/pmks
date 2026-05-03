<?php

namespace App\Filament\Resources\Institutions;

use App\Enums\UserRole;
use App\Filament\Resources\Institutions\Pages\CreateInstitution;
use App\Filament\Resources\Institutions\Pages\EditInstitution;
use App\Filament\Resources\Institutions\Pages\ListInstitutions;
use App\Filament\Resources\Institutions\Schemas\InstitutionForm;
use App\Filament\Resources\Institutions\Tables\InstitutionsTable;
use App\Models\Institution;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InstitutionResource extends Resource
{
    protected static ?string $model = Institution::class;
    protected static ?string $modelLabel = 'Lembaga';
    protected static ?string $pluralModelLabel = 'Data Lembaga';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    // Semua role kecuali Verifikator bisa akses
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::OPERATOR_DESA->value,
        ]) ?? false;
    }

    public static function getNavigationLabel(): string { return 'Data Lembaga'; }
    public static function getNavigationGroup(): string { return 'Data Penduduk'; }
    public static function getNavigationSort(): ?int { return 3; }

    public static function form(Schema $schema): Schema
    {
        return InstitutionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstitutionsTable::configure($table);
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
            'index'  => ListInstitutions::route('/'),
            'create' => CreateInstitution::route('/create'),
            'edit'   => EditInstitution::route('/{record}/edit'),
        ];
    }
}
