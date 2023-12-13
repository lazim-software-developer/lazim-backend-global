<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlatDocumentResource\Pages\CreateFlatDocument;
use App\Filament\Resources\FlatDocumentResource\Pages\EditFlatDocument;
use App\Filament\Resources\FlatDocumentResource\Pages\ListFlatDocuments;
use App\Models\Building\Document;
use Filament\Facades\Filament;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class FlatDocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Document Management';
    protected static ?string $navigationLabel = 'Flat';

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
                                ->placeholder('Document Library')
                                ->getSearchResultsUsing(
                                    fn(string $search) => DB::table('document_libraries')
                                        ->join('building_documentlibraries', function (JoinClause $join) {
                                            $join->on('document_libraries.id', '=', 'building_documentlibraries.documentlibrary_id')
                                                ->where([
                                                    ['building_id', '=', Filament::getTenant()->id],

                                                ]);
                                        })
                                        ->pluck('document_libraries.name', 'document_libraries.id')
                                ),
                            FileUpload::make('url')
                                ->disk('s3')
                                ->directory('dev')
                                ->label('Document')
                                ->required(),
                            Select::make('flat_id')
                                ->relationship('flat', 'property_number')
                                ->searchable()
                                ->preload()
                                ->label('Unit Number'),
                            Select::make('status')
                                ->options([
                                    'submitted' => 'Submitted',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                                ->searchable()
                                ->required()
                                ->placeholder('Status'),
                            TextInput::make('comments'),
                            TextInput::make('name'),
                            //->required(),
                            DatePicker::make('expiry_date')
                                ->rules(['date'])
                                ->required()
                                ->placeholder('Expiry Date'),

                            Hidden::make('owner_association_id')
                                ->default(auth()->user()->owner_association_id),

                            Hidden::make('documentable_type')
                                ->default('App\Models\Building\Flat'),

                            Select::make('documentable_id')
                                ->options(
                                    DB::table('flats')->pluck('property_number', 'id')->toArray()
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->label('Flat Number')
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
            ->modifyQueryUsing(fn(Builder $query) => $query->where('documentable_type', 'App\Models\Building\Flat')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Document Name')
                    ->default('NA')
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
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                
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
            'index' => ListFlatDocuments::route('/'),
            'create' => CreateFlatDocument::route('/create'),
            'edit' => EditFlatDocument::route('/{record}/edit'),
        ];
    }
}
