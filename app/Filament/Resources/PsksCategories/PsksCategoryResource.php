<?php

namespace App\Filament\Resources\PsksCategories;

use App\Enums\UserRole;
use App\Filament\Resources\PsksCategories\Pages\CreatePsksCategory;
use App\Filament\Resources\PsksCategories\Pages\EditPsksCategory;
use App\Filament\Resources\PsksCategories\Pages\ListPsksCategories;
use App\Filament\Resources\PsksCategories\Schemas\PsksCategoryForm;
use App\Filament\Resources\PsksCategories\Tables\PsksCategoriesTable;
use App\Models\PsksCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PsksCategoryResource extends Resource
{
    protected static ?string $model = PsksCategory::class;
    protected static ?string $modelLabel = 'Kategori PSKS';
    protected static ?string $pluralModelLabel = 'Kategori PSKS';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    // Hanya Admin Dinsos yang bisa kelola master data kategori
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
        ]) ?? false;
    }

    public static function getNavigationLabel(): string { return 'Kategori PSKS'; }
    public static function getNavigationGroup(): string { return 'Master Data Kategori'; }
    public static function getNavigationSort(): ?int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return PsksCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PsksCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPsksCategories::route('/'),
            'create' => CreatePsksCategory::route('/create'),
            'edit'   => EditPsksCategory::route('/{record}/edit'),
        ];
    }
}
