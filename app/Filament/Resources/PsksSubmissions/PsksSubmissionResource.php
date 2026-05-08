<?php

namespace App\Filament\Resources\PsksSubmissions;

use App\Filament\Resources\PsksSubmissions\Pages\CreatePsksSubmission;
use App\Filament\Resources\PsksSubmissions\Pages\EditPsksSubmission;
use App\Filament\Resources\PsksSubmissions\Pages\ListPsksSubmissions;
use App\Filament\Resources\PsksSubmissions\Schemas\PsksSubmissionForm;
use App\Filament\Resources\PsksSubmissions\Tables\PsksSubmissionsTable;
use App\Models\PsksSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PsksSubmissionResource extends Resource
{
    protected static ?string $model = PsksSubmission::class;
    protected static ?string $modelLabel = 'Data PSKS';
    protected static ?string $pluralModelLabel = 'Data PSKS';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function getNavigationLabel(): string { return 'Data PSKS'; }
    public static function getNavigationGroup(): string { return 'Pengajuan PMKS & PSKS'; }
    public static function getNavigationSort(): ?int { return 3; }

    public static function form(Schema $schema): Schema
    {
        return PsksSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PsksSubmissionsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'batch',
                'village.kecamatan',
                'category',
                'subject',  // MorphTo — eager load Resident atau Institution sekaligus
                'inputBy',
            ])
            ->when(
                auth()->user()?->isOperatorDesa() && auth()->user()->village_id,
                fn ($q) => $q->where('village_id', auth()->user()->village_id)
            );
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPsksSubmissions::route('/'),
            'create' => CreatePsksSubmission::route('/create'),
            'edit'   => EditPsksSubmission::route('/{record}/edit'),
        ];
    }
}
