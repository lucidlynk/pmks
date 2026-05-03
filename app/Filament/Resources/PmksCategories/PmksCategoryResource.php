<?php

namespace App\Filament\Resources\PmksCategories;

use App\Enums\UserRole;
use App\Filament\Resources\PmksCategories\Pages\CreatePmksCategory;
use App\Filament\Resources\PmksCategories\Pages\EditPmksCategory;
use App\Filament\Resources\PmksCategories\Pages\ListPmksCategories;
use App\Filament\Resources\PmksCategories\Schemas\PmksCategoryForm;
use App\Filament\Resources\PmksCategories\Tables\PmksCategoriesTable;
use App\Models\PmksCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PmksCategoryResource extends Resource
{
    protected static ?string $model = PmksCategory::class;
    protected static ?string $modelLabel = 'Kategori PMKS';
    protected static ?string $pluralModelLabel = 'Kategori PMKS';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    // Hanya Admin Dinsos yang bisa kelola master data kategori
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
        ]) ?? false;
    }

    public static function getNavigationLabel(): string { return 'Kategori PMKS'; }
    public static function getNavigationGroup(): string { return 'Master Data Kategori'; }
    public static function getNavigationSort(): ?int { return 1; }

    public static function form(Schema $schema): Schema
    {
        return PmksCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PmksCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPmksCategories::route('/'),
            'create' => CreatePmksCategory::route('/create'),
            'edit'   => EditPmksCategory::route('/{record}/edit'),
        ];
    }
}
