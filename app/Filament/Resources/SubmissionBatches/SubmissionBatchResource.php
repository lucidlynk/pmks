<?php

namespace App\Filament\Resources\SubmissionBatches;

use App\Enums\UserRole;
use App\Filament\Resources\SubmissionBatches\Pages\CreateSubmissionBatch;
use App\Filament\Resources\SubmissionBatches\Pages\EditSubmissionBatch;
use App\Filament\Resources\SubmissionBatches\Pages\ListSubmissionBatches;
use App\Filament\Resources\SubmissionBatches\Pages\ViewSubmissionBatch;
use App\Filament\Resources\SubmissionBatches\Schemas\SubmissionBatchForm;
use App\Filament\Resources\SubmissionBatches\Tables\SubmissionBatchesTable;
use App\Models\SubmissionBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubmissionBatchResource extends Resource
{
    protected static ?string $model = SubmissionBatch::class;
    protected static ?string $modelLabel = 'Pengajuan';
    protected static ?string $pluralModelLabel = 'Data Pengajuan';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    // Semua role bisa akses (dengan scope berbeda)
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function getNavigationLabel(): string { return 'Data Pengajuan'; }
    public static function getNavigationGroup(): string { return 'Pengajuan PMKS & PSKS'; }
    public static function getNavigationSort(): ?int { return 1; }

    public static function form(Schema $schema): Schema
    {
        return SubmissionBatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubmissionBatchesTable::configure($table);
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
            'index'  => ListSubmissionBatches::route('/'),
            'create' => CreateSubmissionBatch::route('/create'),
            'edit'   => EditSubmissionBatch::route('/{record}/edit'),
            'view'   => ViewSubmissionBatch::route('/{record}'),
        ];
    }
}
