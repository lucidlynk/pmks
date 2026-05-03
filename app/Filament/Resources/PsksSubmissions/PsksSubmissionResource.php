<?php

namespace App\Filament\Resources\PsksSubmissions;

use App\Enums\UserRole;
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

    // Semua role bisa akses (Verifikator hanya lihat)
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
            'index'  => ListPsksSubmissions::route('/'),
            'create' => CreatePsksSubmission::route('/create'),
            'edit'   => EditPsksSubmission::route('/{record}/edit'),
        ];
    }
}
