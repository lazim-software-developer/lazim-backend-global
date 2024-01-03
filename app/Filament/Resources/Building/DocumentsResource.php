<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\DocumentsResource\Pages\CreateDocuments;
use App\Filament\Resources\Building\DocumentsResource\Pages\EditDocuments;
use App\Filament\Resources\Building\DocumentsResource\Pages\ListDocuments;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Building\FlatTenant;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class DocumentsResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Document Management';
    protected static ?string $navigationLabel = 'Vendor';
    protected static bool $shouldRegisterNavigation = true;
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
                        ->getSearchResultsUsing(fn(string $search) => DB::table('document_libraries')
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
                    Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                        ])
                        ->searchable()
                        ->required()
                        ->placeholder('Status'),
                    TextInput::make('comments'),
                    //->required(),
                    DatePicker::make('expiry_date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Expiry Date'),

                    Hidden::make('accepted_by')
                        ->default(auth()->user()->id),

                    Hidden::make('documentable_type')
                        ->default('App\Models\Vendor\Vendor'),

                    Select::make('documentable_id')
                        ->options(
                            DB::table('vendors')->pluck('name', 'id')->toArray()
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->placeholder('Documentable Id'),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('documentable_type', 'App\Models\Vendor\Vendor')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('documentLibrary.name')
                    ->searchable()
                    ->default('NA')
                    ->toggleable()
                    ->limit(50),
                TextColumn::make('url')->label('Document')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('status')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('expiry_date')
                    ->toggleable()
                    ->date(),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                ViewColumn::make('name')->view('tables.columns.document')
                    ->searchable()
                    ->default('NA')
                    ->toggleable(),
                TextColumn::make('documentable_type')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
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
            'index'  => ListDocuments::route('/'),
            'create' => CreateDocuments::route('/create'),
            'edit'   => EditDocuments::route('/{record}/edit'),
        ];
    }
}
