<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\DocumentLibraryResource\Pages;
use App\Filament\Resources\Master\DocumentLibraryResource\RelationManagers;
use App\Models\Master\DocumentLibrary;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentLibraryResource extends Resource
{
    protected static ?string $model = DocumentLibrary::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Select::make('name')
                        ->options([
                            'tl_document' =>'TL Document',
                            'trn_cerftificate'=>'TRN Certificate',
                            'third_party_liability'=>'Third Party Liability Certificate',
                            'risk_assessement'=>'Risk Assessement',
                            'safety_policy'=>'Safety Policy',
                            'bank_details'=>'Bank Details On Company Letter Head With Stamp',
                            'authority_approval'=>'Authority Approval'
                        ])

                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Name')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                    Select::make('type')
                        ->options([
                            'vendor'=>'Vendor',
                            'tenant'=>'Tenant'
                        ]),

                    FileUpload::make('url')
                        ->required()
                        ->disk('s3')
                        ->downloadable()
                        ->previewable()
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->poll('60s')
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->toggleable()
                ->searchable(true, null, true)
                ->limit(50),
            // ViewColumn::make('url')->view('tables.columns.url-column')
            //     ->toggleable(),
            Tables\Columns\TextColumn::make('url')
                ->toggleable()
                ->searchable()
                ->openUrlInNewTab()
                ->limit(50),
            Tables\Columns\TextColumn::make('type')
                ->toggleable()
                ->searchable(true, null, true)
                ->limit(50),
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
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DocumentLibraryResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentLibraries::route('/'),
            'create' => Pages\CreateDocumentLibrary::route('/create'),
            'edit' => Pages\EditDocumentLibrary::route('/{record}/edit'),
        ];
    }
}
