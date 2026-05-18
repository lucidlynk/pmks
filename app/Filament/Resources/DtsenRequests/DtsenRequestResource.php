<?php
namespace App\Filament\Resources\DtsenRequests;
use App\Enums\UserRole;
use App\Filament\Resources\DtsenRequests\Pages\CreateDtsenRequest;
use App\Filament\Resources\DtsenRequests\Pages\ListDtsenRequests;
use App\Filament\Resources\DtsenRequests\Pages\ViewDtsenRequest;
use App\Filament\Resources\DtsenRequests\Schemas\DtsenRequestForm;
use App\Filament\Resources\DtsenRequests\Tables\DtsenRequestsTable;
use App\Models\DtsenRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class DtsenRequestResource extends Resource
{
    protected static ?string $model = DtsenRequest::class;
    protected static ?string $modelLabel = 'Permohonan DTSEN';
    protected static ?string $pluralModelLabel = 'Permohonan DTSEN';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;
    public static function canAccess(): bool
    {
        return auth()->check();
    }
    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole(UserRole::OPERATOR_DESA->value) ?? false;
    }
    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value) ?? false;
    }
    public static function getNavigationLabel(): string
    {
        return 'Permohonan DTSEN';
    }
    public static function getNavigationGroup(): string
    {
        return 'Surat DTSEN';
    }
    public static function getNavigationSort(): ?int
    {
        return 1;
    }
    public static function form(Schema $schema): Schema
    {
        return DtsenRequestForm::configure($schema);
    }
    public static function table(Table $table): Table
    {
        return DtsenRequestsTable::configure($table);
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['village.kecamatan', 'user', 'processedBy', 'residents', 'currentDocument'])->withCount('residents')
            ->when(
                auth()->user()?->isOperatorDesa() && auth()->user()->village_id,
                fn ($q) => $q->where('village_id', auth()->user()->village_id)
            );
    }
    public static function getPages(): array
    {
        return [
            'index'  => ListDtsenRequests::route('/'),
            'create' => CreateDtsenRequest::route('/create'),
            'view'   => ViewDtsenRequest::route('/{record}'),
        ];
    }
}
