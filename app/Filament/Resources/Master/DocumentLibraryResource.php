<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\DocumentLibraryResource\Pages;
use App\Filament\Resources\Master\DocumentLibraryResource\RelationManagers;
use App\Models\Master\DocumentLibrary;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
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
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,])
                    ->schema([
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
                        ->placeholder('Name'),
                    Select::make('type')
                        ->options([
                            'vendor'=>'Vendor',
                            'tenant'=>'Tenant',
                            'owner'=>'Owner'
                        ]),
                    FileUpload::make('url')->label('Document')
                        ->required()
                        ->disk('s3')
                        ->downloadable(true)
                        ->preserveFilenames(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $documents = DocumentLibrary::wherenotNuLL('url');

        return $table
        ->poll('60s')
        ->query($documents)
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->toggleable()
                ->searchable(true, null, true)
                ->limit(50),
            Tables\Columns\TextColumn::make('url')->label('Uploaded Document')
                ->toggleable()
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('type')
                ->toggleable()
                ->searchable(true, null, true)
                ->limit(50),
        ])
        ->defaultSort('created_at', 'desc')
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
