<?php

namespace App\Filament\Resources\PmksSubmissions;

use App\Filament\Resources\PmksSubmissions\Pages\CreatePmksSubmission;
use App\Filament\Resources\PmksSubmissions\Pages\EditPmksSubmission;
use App\Filament\Resources\PmksSubmissions\Pages\ListPmksSubmissions;
use App\Filament\Resources\PmksSubmissions\Schemas\PmksSubmissionForm;
use App\Filament\Resources\PmksSubmissions\Tables\PmksSubmissionsTable;
use App\Models\PmksSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PmksSubmissionResource extends Resource
{
    protected static ?string $model = PmksSubmission::class;
    protected static ?string $modelLabel = 'Data PMKS';
    protected static ?string $pluralModelLabel = 'Data PMKS';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function getNavigationLabel(): string { return 'Data PMKS'; }
    public static function getNavigationGroup(): string { return 'Pengajuan PMKS & PSKS'; }
    public static function getNavigationSort(): ?int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return PmksSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PmksSubmissionsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['batch', 'village', 'resident', 'category', 'inputBy'])
            ->when(
                auth()->user()?->isOperatorDesa() && auth()->user()->village_id,
                fn ($q) => $q->where('village_id', auth()->user()->village_id)
            );
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPmksSubmissions::route('/'),
            'create' => CreatePmksSubmission::route('/create'),
            'edit'   => EditPmksSubmission::route('/{record}/edit'),
        ];
    }
}
