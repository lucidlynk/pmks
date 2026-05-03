<?php

namespace App\Filament\Resources\Residents;

use App\Enums\UserRole;
use App\Filament\Resources\Residents\Pages\CreateResident;
use App\Filament\Resources\Residents\Pages\EditResident;
use App\Filament\Resources\Residents\Pages\ListResidents;
use App\Filament\Resources\Residents\Schemas\ResidentForm;
use App\Filament\Resources\Residents\Tables\ResidentsTable;
use App\Models\Resident;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResidentResource extends Resource
{
    protected static ?string $model = Resident::class;
    protected static ?string $modelLabel = 'Penduduk';
    protected static ?string $pluralModelLabel = 'Data Penduduk';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    // Semua role kecuali Verifikator bisa akses
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::OPERATOR_DESA->value,
        ]) ?? false;
    }

    public static function getNavigationLabel(): string { return 'Data Penduduk'; }
    public static function getNavigationGroup(): string { return 'Data Penduduk'; }
    public static function getNavigationSort(): ?int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return ResidentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResidentsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
        $user = auth()->user();
        if ($user?->isOperatorDesa() && $user->village_id) {
            $query->where('village_id', $user->village_id);
        }
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListResidents::route('/'),
            'create' => CreateResident::route('/create'),
            'edit'   => EditResident::route('/{record}/edit'),
        ];
    }
}
