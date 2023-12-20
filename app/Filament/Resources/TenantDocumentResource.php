<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use App\Models\Building\Document;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use App\Filament\Resources\TenantDocumentResource\Pages;

class TenantDocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Master';
    protected static ?string $navigationLabel = 'Resident Document';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                            TextInput::make('name')->disabled(),
                            Select::make('document_library_id')
                                ->rules(['exists:document_libraries,id'])
                                ->relationship('documentLibrary', 'name')
                                ->disabled()
                                ->searchable()
                                ->placeholder('Document Library'),
                            Select::make('building_id')
                                ->relationship('building', 'name')
                                ->preload()
                                ->disabled()
                                ->default('NA')
                                ->searchable()
                                ->label('Building Name'),
                            Select::make('flat_id')
                                ->relationship('flat', 'property_number')
                                ->preload()
                                ->default('NA')
                                ->disabled()
                                ->searchable()
                                ->label('Unit Number'),
                            FileUpload::make('url')
                                ->disk('s3')
                                ->directory('dev')
                                ->disabled()
                                ->openable(true)
                                ->downloadable(true)
                                ->label('Document')
                                ->columnSpan([
                                    'sm' => 1,
                                    'md' => 1,
                                    'lg' => 2,
                                ]),
                            DatePicker::make('expiry_date')
                                ->rules(['date'])
                                ->required()
                                ->disabled()
                                ->readonly()
                                ->placeholder('Expiry Date'),
                            TextInput::make('comments')
                                ->required()
                                ->disabled(function (Document $record) {
                                    return $record->status != 'submitted';
                                })
                                ->label('Comments'),
                            Select::make('status')
                                ->options([
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                                ->disabled(function (Document $record) {
                                    return $record->status != 'submitted';
                                })
                                ->searchable()
                                ->live(),
                            TextInput::make('remarks')
                                ->rules(['max:255'])
                                ->visible(function (callable $get) {
                                    if ($get('status') == 'rejected') {
                                        return true;
                                    }
                                    return false;
                                })
                                ->disabled(function (Document $record) {
                                    return $record->status != 'submitted';
                                })
                                ->required(),
                        ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('documentable_type', 'App\Models\User\User')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Document Name')
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Building Name')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Unit Number')
                    ->limit(50),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('documentUsers.first_name')
                    ->searchable()
                    ->label('Tenant Name')
                    ->default('NA'),
                ViewColumn::make('Role')->view('tables.columns.role')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('documentable_id')
                    ->relationship('documentUsers', 'first_name', fn (Builder $query) => $query->where('role_id',Role::where('name','Tenant')->first()->id))
                    ->searchable()
                    ->preload()
                    ->label('Resident'),
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', fn (Builder $query) => $query->where('owner_association_id',auth()->user()->owner_association_id))
                    ->searchable()
                    ->preload()
                    ->label('Building'),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenantDocuments::route('/'),
            'edit' => Pages\EditTenantDocument::route('/{record}/edit'),
        ];
    }
}
