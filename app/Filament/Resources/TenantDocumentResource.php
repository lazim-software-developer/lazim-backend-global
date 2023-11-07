<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantDocumentResource\Pages;
use App\Filament\Resources\TenantDocumentResource\RelationManagers;
use App\Models\Building\Document;
use App\Models\TenantDocument;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Forms;
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
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantDocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Document Management';
    protected static ?string $navigationLabel = 'Resident';

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
                        ->required()
                        ->relationship('documentLibrary', 'name')
                        ->preload()
                        ->searchable()
                        ->placeholder('Document Library')
                        ->getSearchResultsUsing(
                            fn (string $search) => DB::table('document_libraries')
                                ->join('building_documentlibraries', function (JoinClause $join) {
                                    $join->on('document_libraries.id', '=', 'building_documentlibraries.documentlibrary_id')
                                        ->where([
                                            ['building_id', '=', Filament::getTenant()->id],

                                        ]);
                                })
                                ->pluck('document_libraries.name', 'document_libraries.id')
                        ),
                    FileUpload::make('url')->label('Document')
                        ->disk('s3')
                        ->directory('dev')
                        ->required()
                        ->downloadable()
                        ->preserveFilenames(),
                    Select::make('status')
                        ->options([
                            'submitted' => 'Submitted',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->searchable()
                        ->required()
                        ->placeholder('Status'),
                    TextInput::make('comments')
                        ->readonly(),
                    DatePicker::make('expiry_date')
                        ->rules(['date'])
                        ->required()
                        ->readonly()
                        ->placeholder('Expiry Date'),

                    // Hidden::make('documentable_type')
                    //     ->default('App\Models\User\User'),
                    // Hidden::make('documentable_id')
                    //     ->default(Auth()->user()->id),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('documentable_type', 'App\Models\User\User')->withoutGlobalScopes())
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
                    ->label('Flat Number')
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
                    ->relationship('documentUsers', 'first_name')
                    ->searchable()
                    ->preload()
                    ->label('Tenant'),
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Building'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
