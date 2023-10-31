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
    protected static ?string $navigationLabel = 'Tenant';

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
                    ->getSearchResultsUsing(fn(string $search) => DB::table('document_libraries')
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
            ->modifyQueryUsing(fn(Builder $query) => $query->where('documentable_type', 'App\Models\User\User')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('name')
                    ->toggleable()
                    ->limit(50),
                TextColumn::make('documentLibrary.name')
                    ->toggleable()
                    ->limit(50),
                TextColumn::make('status')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                TextColumn::make('expiry_date')
                    ->toggleable()
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'create' => Pages\CreateTenantDocument::route('/create'),
            'edit' => Pages\EditTenantDocument::route('/{record}/edit'),
        ];
    }
}
