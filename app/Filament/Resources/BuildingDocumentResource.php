<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuildingDocumentResource\Pages\CreateBuildingDocument;
use App\Filament\Resources\BuildingDocumentResource\Pages\EditBuildingDocument;
use App\Filament\Resources\BuildingDocumentResource\Pages\ListBuildingDocuments;
use App\Models\Building\Document;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BuildingDocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Document Management';
    protected static ?string $navigationLabel = 'Building';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([

                            Select::make('document_library_id')
                                ->rules(['exists:document_libraries,id'])
                                ->required()
                                ->preload()
                                ->relationship('documentLibrary', 'name')
                                ->searchable()
                                ->placeholder('Document Library'),

                            FileUpload::make('url')
                                ->disk('s3')
                                ->directory('dev')
                                ->label('Document')
                                ->required(),
                            TextInput::make('name'),
                            DatePicker::make('expiry_date')
                                ->rules(['date'])
                                ->required()
                                ->placeholder('Expiry Date'),

                            Hidden::make('owner_association_id')
                                ->default(auth()->user()->owner_association_id),

                            Hidden::make('documentable_type')
                                ->default('App\Models\Building\Building'),

                            Select::make('documentable_id')
                                ->options(
                                    DB::table('buildings')->pluck('name', 'id')->toArray()
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->label('Building')
                                ->placeholder('Documentable Id'),
                            Select::make('status')
                                ->options([
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                                ->disabled(function (Document $record) {
                                    return $record->status != null;
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
                                    return $record->status != null;
                                })
                                ->required(),
                        ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('documentable_type', 'App\Models\Building\Building')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('documentLibrary.name')
                    ->searchable()
                    ->default('NA')
                    ->toggleable()
                    ->limit(50),
                TextColumn::make('status')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('expiry_date')
                    ->toggleable()
                    ->date(),
                ViewColumn::make('Building Name')->view('tables.columns.document')
                    ->searchable()
                    ->default('NA')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])->actions([
                    
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
            'index' => ListBuildingDocuments::route('/'),
            'create' => CreateBuildingDocument::route('/create'),
            'edit' => EditBuildingDocument::route('/{record}/edit'),
        ];
    }
}
